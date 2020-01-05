<?php

namespace App\Controller;

use App\Entity\Sensor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $measureRepository = $this->getDoctrine()->getRepository('App:Measure');
        $sensorRepository = $this->getDoctrine()->getRepository('App:Sensor');
        $lastSensorMeasure = [];
        foreach ($sensorRepository->findBy(['id' => [2, 3, 4]]) as $sensor) {
            $lastSensorMeasure[] = [
                'measure' => $measureRepository->getLastMeasureForSensor($sensor),
                'sensor' => $sensor,
            ];
        }

        return $this->render('App:Meteo:home.html.twig', ['last_measures' => $lastSensorMeasure]);
    }

    /**
     * @Route("/history/{id}", name="history")
     * @param Sensor $sensor
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function history(Sensor $sensor)
    {
        return $this->historyOnRange($sensor, new \DateTime('now'), new \DateTime('now'));
    }

    /**
     * @Route("/history/{id}/{start}/{end}", name="history_range")
     *
     * @param Sensor $sensor
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @ParamConverter("start", options={"format": "d-m-Y"})
     * @ParamConverter("end", options={"format": "d-m-Y"})
     *
     * @return Response
     */
    public function historyOnRange(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $today = new \DateTime('now');
        $todayRoute = $this->generateUrl(
            'history_range',
            [
                'id' => $sensor->getId(),
                'start' => $today->format('d-m-Y'),
                'end' => $today->format('d-m-Y')
            ]
        );

        $tomorrow = new \DateTime('tomorrow');
        $weekStart = $tomorrow->modify('last monday');
        $yesterday = new \DateTime('yesterday');
        $weekEnd = $yesterday->modify('next sunday');
        $weekRoute = $this->generateUrl(
            'history_range',
            [
                'id' => $sensor->getId(),
                'start' => $weekStart->format('d-m-Y'),
                'end' => $weekEnd->format('d-m-Y')
            ]
        );

        $monthRoute = $this->generateUrl(
            'history_range',
            [
                'id' => $sensor->getId(),
                'start' => '1-' . date('n') . '-' . date('Y'),
                'end' => date('t') . '-' . date('n') . '-' . date('Y')
            ]
        );

        $yearRoute = $this->generateUrl(
            'history_range',
            [
                'id' => $sensor->getId(),
                'start' => '1-1-' . date('Y'),
                'end' => '31-12-' . date('Y')
            ]
        );

        return $this->render(
            'App:Meteo:history.html.twig',
            [
                'sensor' => $sensor,
                'start' => $start->getTimestamp(),
                'end' => $end->getTimestamp(),
                'todayRoute' => $todayRoute,
                'weekRoute' => $weekRoute,
                'monthRoute' => $monthRoute,
                'yearRoute' => $yearRoute
            ]
        );
    }

    /**
     * @Route("/history/measures/{id}/{start}/{end}", name="measures_range")
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
    public function measures(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $measureRepository = $this->getDoctrine()->getRepository('App:Measure');
        $shader = $this->get('shader');

        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $measures = $measureRepository->getMeasuresBySensorAndRange($sensor, $start, $end);
        $maxMeasure = $measureRepository->getMaxMeasuresBySensorAndRange($sensor, $start, $end);
        $minMeasure = $measureRepository->getMinMeasuresBySensorAndRange($sensor, $start, $end);

        $normalizedMeasures = [];
        foreach ($measures as $measure) {
            $markerSize = 0;
            if ($maxMeasure->getId() === $measure->getId()) {
                $markerColor = 'rgb(239,75,43)';
                $markerSize = 7;
            } elseif ($minMeasure->getId() === $measure->getId()) {
                $markerColor = 'rgb(32,175,204)';
                $markerSize = 7;
            } else {
                $markerColor = $shader->shade((float)$measure->getValue());
            }

            $normalizedMeasures[] = [
                'x' => $measure->getDate()->getTimestamp() * 1000,
                'y' => (float)$measure->getValue(),
                'lineColor' => $shader->shade((float)$measure->getValue()),
                'markerColor' => $markerColor,
                'markerSize' => $markerSize
            ];
        }

        return new JsonResponse($normalizedMeasures);
    }
}
