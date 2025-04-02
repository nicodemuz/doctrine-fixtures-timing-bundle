<?php

declare(strict_types=1);

namespace Nicodemuz\DoctrineFixturesTimingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineFixturesTimingBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}