<?php

namespace Nicodemuz\DoctrineFixturesTimingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DoctrineFixturesTimingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // No configuration needed yet
    }

    public function getAlias(): string
    {
        return 'doctrine_fixtures_timing';
    }
}