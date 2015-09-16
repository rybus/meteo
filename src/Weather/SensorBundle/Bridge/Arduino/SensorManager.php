<?php

namespace Weather\SensorBundle\Bridge\Arduino;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Weather\SensorBundle\Entity\Measure;
use Weather\SensorBundle\Entity\Sensor;

/**
 * This class is used for communication with Arduino Yùn device.
 */
class SensorManager
{
    /** @const string */
    const THERMOMETRE_TYPE = 'Thermomètre';

    /** @const string */
    const HUMIDITY_SENSOR_TYPE = 'Capteur d\'humidité';

    /** @constr string */
    const IS_ARDUINO_UP_COMMAND = 'up';

    /** @constr string */
    const IS_SENSOR_CONNECTED_COMMAND = 'sensor';

    /** @var EntityManager */
    protected $entityManager;

    /** @var  string */
    protected $arduinoAddress;

    /** @var  boolean */
    protected $state;

    public function __construct(EntityManager $entityManager, $arduinoAddress)
    {
        $this->entityManager = $entityManager;
        $this->state = false;
        $this->arduinoAddress = 'http:// ' .$arduinoAddress . '/arduino/';
    }

    /**
     * Basic test to check if Arduino device is up.
     *
     * @return bool
     */
    public function isArduinoUp()
    {
        if(!$this->state) {
            $url = $this->arduinoAddress . self::IS_ARDUINO_UP_COMMAND;
            $headers = @get_headers($url, 1);
            if ($headers === false) {
                $this->state = false;
            } else {
                $this->state = true;
            }
        }
        var_dump($this->state);

        return $this->state;
    }

    /**
     * Get all the connected device
     *
     * @return ArrayCollection
     */
    public function getConnectedDevices()
    {
        $connectedDevices = new ArrayCollection();
        $sensorRepository = $this->entityManager->getRepository('WeatherSensorBundle:Sensor');
        $devices = $sensorRepository->findAll();
        foreach ($devices as $device) {
            if ($this->isDeviceConnected($device)) {
                $connectedDevices[] = $device;
            }
        }

        return $connectedDevices;
    }

    /**
     * Refresh last seen date of all connected devices.
     */
    public function refreshConnectedDevices()
    {
        $devices = $this->entityManager->getRepository('WeatherSensorBundle:Sensor')->findAll();

        foreach ($devices as $device) {
            if (null !== $measureValue = $this->isDeviceConnected($device)) {
                $device->setLastSeen(new \DateTime());
                $device->setConnected(true);
                $measure = new Measure();
                $measure->setValue($measureValue);
                $measure->setDate(new \DateTime());
                $device->addMeasure($measure);
            } else {
                $device->setConnected(false);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Get the measure for the given sensor. Returns false if device not connected.
     *
     * @param Sensor $sensor
     *
     * @return float
     */
    public function getDeviceMeasure(Sensor $sensor)
    {
        $rom = $this->encodeRom($sensor->getRom());

        $json_url = $this->arduinoAddress . self::IS_SENSOR_CONNECTED_COMMAND . '/' . $rom;
        $json = file_get_contents($json_url);
        $data = json_decode($json);

        return $data->measure;
    }

    /**
     * Test wether a device is connected or not.
     *
     * @param Sensor $sensor
     * @return float|null
     */
    public function isDeviceConnected(Sensor $sensor)
    {
        if (!$this->isArduinoUp()) {
            return null;
        }

        $measure = $this->getDeviceMeasure($sensor);
        if($measure === '-127.00') {
            return null;
        }

        return $measure;
    }

    /**
     * Get a rom identifier so Arduino can handle it
     *
     * @param $rom
     *
     * @return string
     */
    public function encodeRom($rom)
    {
        $encodedRom = '';
        for($i = 0; $i < strlen($rom); $i++) {
            $encodedRom .= $rom[$i];
            if($i%2 && $i != (strlen($rom) -1)) {
                $encodedRom .= ',';
            }
        }

        return $encodedRom;
    }

    /**
     * Transform raw data from Arduino device into Sensor objects
     *
     * @param $json
     *
     * @return Sensor
     */
    public function transform($json)
    {
        $sensor = new Sensor();
        if($json->type == 'thermometer') {
            $type = $this->getType(self::THERMOMETRE_TYPE);
        } else if($json->type == 'humidity_sensor') {
            $type = $this->getType(self::HUMIDITY_SENSOR_TYPE);
        }
        $sensor->setType($type);
        $sensor->setRom($json->rom);
        $sensor->setLastSeen(new \DateTime());

        return $sensor;
    }

    /**
     * Get SensorType given the raw Arduino type
     *
     * @param $type
     *
     * @return SensorType
     */
    public function getType($type)
    {
        $sensorTypeRepository = $this->entityManager->getRepository('WeatherSensorBundle:SensorType');
        $type = $sensorTypeRepository->findOneBy([
            'name' => $type
        ]);

        return $type;
    }
}