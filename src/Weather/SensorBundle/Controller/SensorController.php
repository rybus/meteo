<?php

namespace Weather\SensorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Weather\SensorBundle\Entity\Measure;
use Weather\SensorBundle\Entity\Sensor;
use Weather\SensorBundle\Form\SensorType as FormSensorType;
use Weather\SensorBundle\Entity\SensorType;

class SensorController extends Controller
{
    /**
     * Shows all the sensor current value
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('WeatherSensorBundle:Sensor');
        $sensors = $repository->getSensorsWithLastMeasures();

        return $this->render(
            'WeatherSensorBundle::index.html.twig',
            [
                'sensors' => $sensors,
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statsAction()
    {
        $repository = $this->getDoctrine()->getRepository('WeatherSensorBundle:Sensor');
        $sensors = $repository->findAll();

        return $this->render(
            'WeatherSensorBundle:Stats:index.html.twig',
            [
                'sensors' => $sensors,
            ]
        );
    }

    /**
     * Show parameters page such as list of sensor, user management
     * @TODO: move into separate bundle.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsAction()
    {
        return $this->render('WeatherSensorBundle:Sensor:settings.html.twig');
    }

    /**
     * Shows all the sensor and their characteristics.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSensorsAction()
    {
        $repository = $this->getDoctrine()->getRepository('WeatherSensorBundle:Sensor');
        $sensors = $repository->findAll();

        return $this->render(
            'WeatherSensorBundle:Sensor:sensors.html.twig',
            [
                'sensors' => $sensors,
            ]
        );
    }

    /**
     * Get and refresh all information about connected sensors.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function refreshAction()
    {
        $sensorManager = $this->get('weather_sensor.bridge.arduino');
        $sensorManager->refreshConnectedDevices();

        return $this->redirect($this->generateUrl('weather_sensor_homepage'));
    }

    /**
     * Shows global statistics for a Sensor. By default, it shows the day statistics
     *
     * @param Sensor $sensor
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSensorAction(Sensor $sensor)
    {
        $now = new \DateTime();
        $beginOfDay = clone $now;
        $beginOfDay->setTime(0, 0, 0);
        $endOfDay = clone $beginOfDay;
        $endOfDay->modify('tomorrow');
        $endOfDay->modify('1 second ago');

        return $this->showSensorByPeriodAction($sensor, $beginOfDay, $endOfDay);
    }

    /**
     * Shows statistics for a Sensor and a given period
     *
     * @param Sensor    $sensor    the sensor we want to show statistics
     * @param \DateTime $startDate the start date for statistics
     * @param \DateTime $endDate   the end date for the statistics
     *
     * @ParamConverter("startDate", options={"format": "Y-m-d"})
     * @ParamConverter("endDate",   options={"format": "Y-m-d"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSensorByPeriodAction(Sensor $sensor, \Datetime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        $repository = $this->getDoctrine()->getRepository('WeatherSensorBundle:Measure');

        $minMeasure = $repository->getMinimumMeasure($sensor, $startDate, $endDate);
        $maxMeasure = $repository->getMaximumMeasure($sensor, $startDate, $endDate);
        $avgMeasure = $repository->getAverageMeasure($sensor, $startDate, $endDate);

        return $this->render(
            'WeatherSensorBundle:Sensor:sensor.html.twig',
            [
                'sensor' => $sensor,
                'startTime' => $startDate,
                'endTime' => $endDate,
                'maxMeasure' => $maxMeasure['max_measure'],
                'minMeasure' => $minMeasure['min_measure'],
                'avgMeasure' => $avgMeasure['avg_measure'],
            ]
        );
    }

    /**
     * Edit a Sensor
     *
     * @param Request $request
     * @param Sensor  $sensor the Sensor to edit
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editSensorAction(Request $request, Sensor $sensor)
    {
        $form = $this->createForm(new FormSensorType(), $sensor);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sensor);
            $entityManager->flush($sensor);

            return $this->redirect($this->generateUrl('weather_sensors_show_all'));
        }

        return $this->render(
            'WeatherSensorBundle:Sensor:new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Import a new Sensor after checking that this is a new one (sensor list page).
     *
     * @param string     $rom        the ROM address of the sensor
     * @param SensorType $sensorType the type of sensor (i.e : thermometer, humidity sensory, light sensor)
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function importSensorAction($rom, SensorType $sensorType)
    {
        $sensor = new Sensor();
        $sensor->setName("Sensor ".time());
        $sensor->setType($sensorType);
        $sensor->setRom($rom);
        $sensor->setLastSeen(new \DateTime());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($sensor);
        $entityManager->flush($sensor);

        return $this->redirect(
            $this->generateUrl(
                'weather_sensors_edit',
                [
                    'id' => $sensor->getId(),
                ]
            )
        );
    }

    /**
     * Generates csv file for further interpretation by JS libraries.
     *
     * @param Sensor $sensor
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function csvSensorAction(Sensor $sensor)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('WeatherSensorBundle:Measure');
        $measures = $repository->findBy(
            [
                'sensor' => $sensor,
            ]
        );

        $response = $this->render(
            'WeatherSensorBundle:Measure:csv.html.twig',
            [
                'measures' => $measures,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="teams.csv"');

        return $response;
    }

    /**
     * Generates csv file for further interpretation by JS libraries.
     *
     * @param Sensor    $sensor
     * @param \DateTime $startDate the start date for statistics
     * @param \DateTime $endDate   the end date for the statistics
     *
     * @ParamConverter("startDate", options={"format": "Y-m-d"})
     * @ParamConverter("endDate",   options={"format": "Y-m-d"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function csvSensorByPeriodAction(Sensor $sensor, \DateTime $startDate, \DateTime $endDate)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('WeatherSensorBundle:Measure');
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $measures = $repository->getMeasuresBySensorAndByPeriod(
            $sensor,
            $startDate,
            $endDate
        );

        $response = $this->render(
            'WeatherSensorBundle:Measure:csv.html.twig',
            [
                'measures' => $measures,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="teams.csv"');

        return $response;
    }
}
