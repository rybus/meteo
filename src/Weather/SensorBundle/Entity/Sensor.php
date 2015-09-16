<?php

namespace Weather\SensorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Sensor
 */
class Sensor
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
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $lastSeen;

    /**
     * @var integer
     */
    private $rom;

    /**
     * @var SensorType
     */
    protected $type;

    /**
     * @var ArrayCollection
     */
    protected $measures;

    /**
     * @var boolean
     */
    protected $connected;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->created = new \DateTime();
    }

    /**
     * @return boolean
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @param boolean $connected
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeasures()
    {
        return $this->measures;
    }

    /**
     * @param ArrayCollection $measures
     */
    public function setMeasures($measures)
    {
        $this->measures = $measures;
    }

    public function addMeasure(Measure $measure)
    {
        $measure->setSensor($this);
        $this->measures->add($measure);
    }

    public function removeMeasure(Measure $measure)
    {
        $this->measures->removeElement($measure);
    }

    public function getMaxMeasure()
    {
        $max = null;
        foreach ($this->measures as $measure) {
            if (null === $max) {
                $max = $measure;
            }
            if ($measure->getValue() > $max->getValue()) {
                $max = $measure;
            }
        }

        return $max;
    }

    public function getMinMeasure()
    {
        $max = null;
        foreach ($this->measures as $measure) {
            if (null === $max) {
                $max = $measure;
            }
            if ($measure->getValue() < $max->getValue()) {
                $max = $measure;
            }
        }

        return $max;
    }

    public function getAvgMeasure()
    {
        $sum = 0;
        foreach ($this->measures as $measure) {
            $sum += $mean = $measure->getValue();
        }

        return $sum/count($this->measures);
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
     * @return SensorType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param SensorType $type
     */
    public function setType(SensorType $type)
    {
        $this->type = $type;
    }

    /**
     * Set name
     *
     * @param string $name
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
     * Set created
     *
     * @param \DateTime $created
     * @return Sensor
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastSeen
     *
     * @param \DateTime $lastSeen
     * @return Sensor
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * Get lastSeen
     *
     * @return \DateTime 
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * Set rom
     *
     * @param integer $rom
     * @return Sensor
     */
    public function setRom($rom)
    {
        $this->rom = $rom;

        return $this;
    }

    /**
     * Get rom
     *
     * @return integer 
     */
    public function getRom()
    {
        return $this->rom;
    }
}
