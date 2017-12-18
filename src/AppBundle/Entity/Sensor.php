<?php

namespace AppBundle\Entity;

/**
 * Sensor
 */
class Sensor
{
    /** @const string */
    const HUMIDITY = 1;

    /** @const string */
    const TEMPERATURE = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;


    /** @var string*/
    private $supportedMeasure;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Sensor
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSupportedMeasure()
    {
        return $this->supportedMeasure;
    }

    /**
     * @param $supportedMeasure
     * @return $this
     */
    public function setSupportedMeasure($supportedMeasure)
    {
        $this->supportedMeasure = $supportedMeasure;

        return $this;
    }
}

