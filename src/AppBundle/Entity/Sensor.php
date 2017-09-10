<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Sensor
 */
class Sensor
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /** @var MeasureType[] $supportedMeasureType */
    private $supportedMeasureTypes;

    /**
     * Sensor constructor.
     */
    public function __construct()
    {
        $this->supportedMeasureTypes = new ArrayCollection();
    }


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
     * @return MeasureType[]
     */
    public function getSupportedMeasureTypes()
    {
        return $this->supportedMeasureTypes;
    }

    /**
     * @param ArrayCollection $supportedMeasureType
     *
     * @return $this
     */
    public function setSupportedMeasureTypes(ArrayCollection $supportedMeasureType)
    {
        $this->supportedMeasureTypes = $supportedMeasureType;

        return $this;
    }

    /**
     * @param MeasureType $measureType
     *
     * @return $this
     */
    public function addSupportedMeasureType(MeasureType $measureType)
    {
        if (!$this->supportedMeasureTypes->contains($measureType)) {
            $this->supportedMeasureTypes->add($measureType);
        }

        return $this;
    }

    /**
     * @param MeasureType $measureType
     * 
     * @return $this
     */
    public function removeSupportedMeasureType(MeasureType $measureType)
    {
        if ($this->supportedMeasureTypes->contains($measureType)) {
            $this->supportedMeasureTypes->removeElement($measureType);
        }

        return $this;
    }
}

