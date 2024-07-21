<?php
declare(strict_types=1);

namespace ElityDEV\Utils\Money\Bridge\Symfony\Serializer\Normalizer;

use Money\Currency;
use Money\Money;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MoneyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): ?array
    {
        if ($object === null || ($object->getAmount() === null && $object->getCurrency() === null)) {
            return null;
        }

        if (!$object instanceof Money) {
            throw new \LogicException();
        }

        return [
            'amount' => (int)$object->getAmount() / 100,
            'currency' => $object->getCurrency(),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Money;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): ?Money
    {
        if ($data === null || ($data['amount'] === null && $data['currency'] === null)) {
            return null;
        }

        if ($data === 0) {
            return new Money(0, new Currency($data['currency']));
        }

        return new Money(
        // convert any input to float with fixed 2 decimal places and remove decimal point, moneyphp expects integer/string
            (string)((int)str_replace('.', '', number_format((float)$data['amount'], 2, '.', ''))),
            new Currency($data['currency'])
        );
    }


    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Money::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Money::class => true,
        ];
    }
}
