<?php

namespace Nicodemuz\DoctrineFixturesTimingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineFixturesTimingBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}