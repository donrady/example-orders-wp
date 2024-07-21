<?php
declare(strict_types=1);

namespace ElityDEV\Utils\Money\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Money\Currency;

class CurrencyType extends Type
{
    public function getName(): string
    {
        return 'currency';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (!isset($column['length'])) {
            $column['length'] = 3;
        }

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Currency
    {
        return $value !== null ? new Currency($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if($value === null) {
            return null;
        }

        if (!$value instanceof Currency) {
            throw new \LogicException();
        }

        return $value->getCode();
    }
}
