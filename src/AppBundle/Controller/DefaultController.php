<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sensor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $measureRepository = $this->getDoctrine()->getRepository('AppBundle:Measure');
        $sensorRepository = $this->getDoctrine()->getRepository('AppBundle:Sensor');
        $lastSensorMeasure = [];

        foreach ($sensorRepository->findAll() as $sensor) {
            $lastSensorMeasure[] = [
                'measure' => $measureRepository->getLastMeasureForSensor($sensor),
                'sensor' => $sensor
            ];
        }

        return $this->render('AppBundle:Meteo:home.html.twig', ['last_measures' => $lastSensorMeasure]);
    }

    /**
     * @Route("/history/{id}", name="history")
     * @param Request $request
     * @param Sensor $sensor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function history(Request $request, Sensor $sensor)
    {
        return $this->render('AppBundle:Meteo:history.html.twig', ['sensor' => $sensor]);
    }

    /**
     * @Route("/history/measures/{id}/{start}/{end}", name="measures_range")
     *
     * @param Request $request
     * @param Sensor $sensor
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @ParamConverter("start", options={"format": "d-m-Y"})
     * @ParamConverter("end", options={"format": "d-m-Y"})
     *
     * @return JsonResponse
     */
    public function measures(Request $request, Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $measureRepository = $this->getDoctrine()->getRepository('AppBundle:Measure');

        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $measures = $measureRepository->getMeasuresBySensorAndRange($sensor, $start, $end);
        $normalizedMeasures = [];
        foreach ($measures as $measure) {
            $normalizedMeasures[] = [
                'x' => $measure->getDate()->getTimestamp() * 1000,
                'y' => (float) $measure->getValue()
            ];
        }

        return new JsonResponse($normalizedMeasures);
    }
}
