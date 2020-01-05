<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
           new TwigFilter('diff', [$this, 'diff'])
        ];
    }

    public function diff(\DateTime $date)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        return $now->diff($date)->format("%i");
    }
}