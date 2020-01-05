<?php

namespace App\Service;

/**
 * This service allows to compute colors based on min and max colors and given color
 *
 * @author Remy Betus <remy@betus.fr>
 */
class Shader
{
    /** @var float */
    protected $minTemperature;

    /** @var float */
    protected $maxTemperature;

    /** @var array r g b */
    protected $fromRGBColor;

    /** @var array r g b */
    protected $toRGBColor;

    /**
     * Shade constructor.
     * @param $minTemperature
     * @param $maxTemperature
     * @param $fromRGBColor
     * @param $toRGBColor
     */
    public function __construct($minTemperature, $maxTemperature, array $fromRGBColor, array $toRGBColor)
    {
        $this->minTemperature = $minTemperature;
        $this->maxTemperature = $maxTemperature;
        $this->fromRGBColor = $fromRGBColor;
        $this->toRGBColor = $toRGBColor;
    }

    /**
     * @param float $temperature in Degree Celsius
     *
     * @return string HEX color
     */
    public function shade($temperature)
    {
        $color = $this->getShade($this->getFactor($temperature));

        return sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
    }

    /**
     * Gives a percentage between minimum supported temperature and max.
     *
     * @param $temperature
     *
     * @return float
     */
    public function getFactor($temperature)
    {
        return ($temperature - $this->minTemperature)/($this->maxTemperature-$this->minTemperature);
    }

    public function getShade($factor)
    {
        return [
            $factor * ($this->toRGBColor[0] - $this->fromRGBColor[0])+$this->fromRGBColor[0],
            $factor * ($this->toRGBColor[1] - $this->fromRGBColor[1])+$this->fromRGBColor[1],
            $factor * ($this->toRGBColor[2] - $this->fromRGBColor[2])+$this->fromRGBColor[2],
        ];
    }
}