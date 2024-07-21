<?php
declare(strict_types=1);

namespace App\Api\OpenApi;

abstract class MoneySchema
{
    public static function addToSchema(\ArrayObject &$schemas): void
    {
        $schemas['Money'] = $schemas['Money.jsonld'] = new \ArrayObject(
            [
                'type' => 'object',
                'properties' => [
                    'amount' => [
                        'type' => 'number',
                        'format' => 'double',
                        'example' => 99.99,
                    ],
                    'currency' => [
                        'type' => 'string',
                        'example' => 'EUR',
                    ],
                ],
            ]
        );

        foreach ($schemas as $key => $schema) {
            if (str_contains($key, 'Money-') || str_contains($key, 'Money.jsonld-')) {
                $schemas[$key] = ['$ref' => '#/components/schemas/Money'];
            }
        }
    }
}
