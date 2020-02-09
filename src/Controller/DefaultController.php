<?php

namespace App\Controller;

use App\Entity\Sensor;
use App\Entity\Measure;
use App\Service\Shader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    const ONE_HOUR = 3600;
    
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Shader $shader)
    {
        $measureRepository = $this->getDoctrine()->getRepository('App:Measure');
        $sensorRepository = $this->getDoctrine()->getRepository('App:Sensor');
        $lastSensorMeasure = [];
        foreach ($sensorRepository->findBy(['id' => [2, 3, 4]]) as $sensor) {
            $measure = $measureRepository->getLastMeasureForSensor($sensor);
            $lastSensorMeasure[] = [
                'measure' => $measure,
                'sensor' => $sensor,
                'color' => $shader->shade($measure->getValue())
            ];
        }

        return $this->render('home.html.twig', ['last_measures' => $lastSensorMeasure]);
    }

    /**
     * @Route("/history/{start}/{end}", name="history_range")
     * @Route("/history/{start}", name="history_from")
     * @Route("/history", name="history_recent")
     *
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @ParamConverter("start", options={"format": "d-m-Y"})
     * @ParamConverter("end", options={"format": "d-m-Y"})
     *
     * @return Response
     */
    public function history(\DateTime $start = null, \DateTime $end = null)
    {
        if (null === $start) {
            $start = new \DateTime('24 hours ago');
        }

        if (null === $end) {
            $end = new \DateTime('now');
        }

        $today = new \DateTimeImmutable('now');
 
        return $this->render(
            'history.html.twig',
            [
                'start' => $start->getTimestamp(),
                'end' => $end->getTimestamp(),
                'todayRoute' => $this->generateUrl('history_from', ['start' => $today->modify('2 days ago')->format('d-m-Y')]),
                'weekRoute'  => $this->generateUrl('history_from', ['start' => $today->modify('2 weeks ago')->format('d-m-Y')]),
                'monthRoute' => $this->generateUrl('history_from', ['start' => $today->modify('2 months ago')->format('d-m-Y')]),
                'yearRoute' => $this->generateUrl('history_from', ['start' => $today->modify('1 year ago')->format('d-m-Y')]),
            ]
        );
    }

    /**
     * @Route("/today/{id}", name="today")
     * 
     *  @param Sensor $sensor
     *
     * @return JsonResponse
     */
    public function today(Shader $shader, Sensor $sensor)
    {
        $today = new \DateTime('now');
        $beginingOfToday = clone $today->setTime(0, 0, 0);
        $endOfToday = clone $today->setTime(23,59,59);

        $measureRepository = $this->getDoctrine()->getRepository('App:Measure');

        $measures = $measureRepository->getMeasuresBySensorAndRange($sensor, $beginingOfToday, $endOfToday);
        $maxMeasure = $measureRepository->getMaxMeasuresBySensorAndRange($sensor, $beginingOfToday, $endOfToday);
        $minMeasure = $measureRepository->getMinMeasuresBySensorAndRange($sensor, $beginingOfToday, $endOfToday);

        foreach ($measures as $measure) {
            $normalizedMeasures[] = $this->normalizeMeasure(
                $shader,
                $maxMeasure,
                $minMeasure,
                $measure->getValue(),
                $measure->getDate()->getTimestamp()
            );
        }

        return new JsonResponse($normalizedMeasures);
    }

    /**
     * @Route("/history/measures/{id}/{start}/{end}", name="measures_range")
     * @Route("/history/measures/{id}/{start}", name="measures_from")
     *
     * @param Sensor $sensor
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @ParamConverter("start", options={"format": "d-m-Y"})
     * @ParamConverter("end", options={"format": "d-m-Y"})
     *
     * @return JsonResponse
     */
    public function measures(Shader $shader, Sensor $sensor, \DateTime $start, \DateTime $end = null)
    {
        $today = new \DateTimeImmutable('now');
        $start->setTime($today->format('H'), $today->format('i'));

        if (null === $end) {
            $end = new \DateTime('now');
        }
        $end->setTime($today->format('H'), $today->format('i'));

        $measureRepository = $this->getDoctrine()->getRepository('App:Measure');

        $measures = $measureRepository->getMeasuresBySensorAndRange($sensor, $start, $end);
        $maxMeasure = $measureRepository->getMaxMeasuresBySensorAndRange($sensor, $start, $end);
        $minMeasure = $measureRepository->getMinMeasuresBySensorAndRange($sensor, $start, $end);

        $windowInSeconds = $this->getMeasureWindow($start, $end);

        $normalizedMeasures = [];
        $means = [];

        foreach ($measures as $measure) {
            $shift = $measure->getDate()->getTimestamp() % $windowInSeconds;
            $windowStart = $measure->getDate()->getTimestamp() - $shift;
            $meanTimestamp = $windowStart + ($windowInSeconds/2);

            if (!isset($means[$meanTimestamp])) {
                $means[$meanTimestamp] = ['value' => $measure->getValue(), 'count' => 1];
            } elseif ($means[$meanTimestamp]['count'] > 0) {
                $means[$meanTimestamp]['value'] = ($means[$meanTimestamp]['count'] * $means[$meanTimestamp]['value'] + $measure->getValue())/(++$means[$meanTimestamp]['count']);
            }
        }

        foreach ($means as $timestamp => $mean) {
            $normalizedMeasures[] = $this->normalizeMeasure(
                $shader,
                $maxMeasure,
                $minMeasure,
                $mean['value'],
                $timestamp
            );             
        }

        return new JsonResponse($normalizedMeasures);
    }

    private function normalizeMeasure(Shader $shader, Measure $maxMeasure, Measure $minMeasure, float $measure, int $x)
    {
        $markerSize = 0;
        $markerColor = $shader->shade($measure);
        if ($maxMeasure->getValue() == $measure) {
            $markerColor = $shader->getWarmestColor();
            $markerSize = 0;
        } elseif ($minMeasure->getValue() == $measure) {
            $markerColor = $shader->getCoolestColor();
            $markerSize = 0;
        } 

        return [
            'x' => $x*1000,
            'y' => (float) number_format($measure, 1, '.', ''),
            'label' => (float) number_format($measure, 1, '.', '') . '°C à ' . date('d/m/Y H:i', $x),
            'lineColor' => $shader->shade($measure),
            'markerColor' => $markerColor,
            'markerSize' => $markerSize
        ];
    }

    private function getMeasureWindow(\DateTime $start, \DateTime $end)
    {
        $interval = $start->diff($end);
        
        if ($interval->days > 30*3) {
            return self::ONE_HOUR*24;
        }

        return self::ONE_HOUR/5;
    }
}
