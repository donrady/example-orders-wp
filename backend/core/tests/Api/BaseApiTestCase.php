<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

abstract class BaseApiTestCase extends ApiTestCase
{
    protected static function createClient(
        array $kernelOptions = [],
        array $defaultOptions = [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ],
    ): Client
    {
        return parent::createClient($kernelOptions, $defaultOptions);
    }

    /** @throws Exception */
    protected function getEntityManager(): ?object
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
