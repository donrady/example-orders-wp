<?php

declare(strict_types=1);

namespace ElityDEV\Utils\Money\Bridge\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use ElityDEV\Utils\Money\Bridge\Doctrine\MoneyIntegrated;
use Money\Money;
use ReflectionObject;

final class NullableMoneyEventSubscriber implements EventSubscriber
{
    private ?string $parentClass = null;

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof MoneyIntegrated) {
            return;
        }

        $objectReflection = new ReflectionObject($entity);
        foreach ($objectReflection->getProperties() as $property) {
            $value = $property->isInitialized($entity) ? $property->getValue($entity) : null;
            if ($value instanceof Money && $this->allPropertiesAreNull($value)) {
                $property->setValue($entity, null);
            }
            // nested Embeddable support
            if ($value instanceof MoneyIntegrated && $this->parentClass !== $value::class) {
                $this->parentClass = $objectReflection->getName();
                $this->postLoad(new LifecycleEventArgs($value, $args->getObjectManager()));
            }
        }
    }

    private function allPropertiesAreNull(Money $object): bool
    {
        return $object->getAmount() === null && $object->getCurrency() === null;
    }
}