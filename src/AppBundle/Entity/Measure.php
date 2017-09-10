<?php

namespace AppBundle\Entity;

/**
 * Measure
 */
class Measure
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $value;

    /**
     * @var \DateTime
     */
    private $date;

    /** @var MeasureType $measureType */
    private $measureType;


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
     * @return MeasureType
     */
    public function getMeasureType()
    {
        return $this->measureType;
    }

    /**
     * @param MeasureType $measureType
     */
    public function setMeasureType(MeasureType $measureType)
    {
        $this->measureType = $measureType;
    }

    /**
     * Set value
     *
     * @param float $value
     *
     * @return Measure
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Measure
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

