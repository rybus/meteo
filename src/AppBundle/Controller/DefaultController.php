<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
