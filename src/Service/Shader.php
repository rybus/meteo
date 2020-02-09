<?php

namespace App\Service;

/**
 * This service allows to compute colors shades based on min and max colors and given color
 *
 * @author Remy Betus <remy@betus.fr>
 */
class Shader
{
    /** @var float */
    protected $minTemperature;

    /** @var float */
    protected $maxTemperature;

    /** @var int degree on the color wheel from 0 to 360. 0 is red, 120 is green, 240 is blue.*/
    protected $fromHueColor;

    /** @var int degree on the color wheel from 0 to 360. 0 is red, 120 is green, 240 is blue. */
    protected $toHueColor;

    /**
     * @param float $minTemperature
     * @param float $maxTemperature
     * @param int   $fromHueColor
     * @param int   $toHueColor
     */
    public function __construct(float $minTemperature, float $maxTemperature, int $fromHueColor, int $toHueColor)
    {
        $this->minTemperature = $minTemperature;
        $this->maxTemperature = $maxTemperature;
        $this->fromHueColor = $fromHueColor;
        $this->toHueColor = $toHueColor;
    }

    /**
     * @param float $temperature in Degree Celsius
     *
     * @return string HEX color
     */
    public function shade($temperature)
    {
        $hue = $this->getHue(
            $this->getFactor($temperature)
        );

        return 'rgb(' . implode(',', ColorManipulation::hslToRgb($hue)) . ')';
    }

    public function getWarmestColor()
    {
        $color = $this->shade($this->maxTemperature);
    }

    public function getCoolestColor()
    {
        $color = $this->shade($this->minTemperature);
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
        if ($temperature >= $this->maxTemperature) {
            return 100;
        }
        else if ($temperature <= $this->minTemperature) {
            return 0;
        }

        return ($temperature - $this->minTemperature)/($this->maxTemperature-$this->minTemperature);
    }

    public function getHue($factor)
    {
        $range = 360 - abs($this->maxTemperature - $this->minTemperature);


        $hue = intval($this->minTemperature - ($factor * $range));

        return ($hue < 0) ? 360 + $hue : $hue;
    }
}
