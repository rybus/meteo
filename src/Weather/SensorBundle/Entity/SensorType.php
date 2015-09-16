<?php

namespace Weather\SensorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SensorType
 */
class SensorType
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var ArrayCollection
     */
    protected $sensors;

    /**
     * @var string
     */
    protected $chipset;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sensors = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getChipset()
    {
        return $this->chipset;
    }

    /**
     * @param string $chipset
     */
    public function setChipset($chipset)
    {
        $this->chipset = $chipset;
    }

    /**
     * @return ArrayCollection
     */
    public function getSensors()
    {
        return $this->sensors;
    }

    /**
     * @param ArrayCollection $sensors
     */
    public function setSensors($sensors)
    {
        $this->sensors = $sensors;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SensorType
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

    public function __toString()
    {
        return $this->name. ' (' .$this->symbol. ')';
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return SensorType
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return string 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set symbol
     *
     * @param string $symbol
     * @return SensorType
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string 
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
