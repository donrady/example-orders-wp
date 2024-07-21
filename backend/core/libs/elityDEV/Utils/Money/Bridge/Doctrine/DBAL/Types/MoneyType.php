<?php
declare(strict_types=1);

namespace ElityDEV\Utils\Money\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class MoneyType extends IntegerType
{
    public const string NAME = 'money';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return parent::convertToDatabaseValue((int)$value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
        return (string)parent::convertToPHPValue($value, $platform);
    }
}
