<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function init(): void
    {
        date_default_timezone_set($this->getContainer()->getParameter('timezone'));
        parent::init();
    }
}
