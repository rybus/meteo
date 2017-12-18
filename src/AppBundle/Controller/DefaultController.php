<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sensor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/history/measures/{id}", name="measures")
     * @param Request $request
     * @param Sensor $sensor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function measures(Request $request, Sensor $sensor)
    {
        $measureRepository = $this->getDoctrine()->getRepository('AppBundle:Measure');

        $measures = $measureRepository->findBy(['sensor' => $sensor]);
        $normalizedMeasures = [];
        foreach ($measures as $measure) {
            $normalizedMeasures[] = [
                'x' => $measure->getDate()->getTimestamp() * 1000,
                'y' => (int)$measure->getValue()
            ];
        }

        return new JsonResponse($normalizedMeasures);
    }
}
