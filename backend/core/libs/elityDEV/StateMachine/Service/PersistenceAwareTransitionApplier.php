<?php

namespace ElityDEV\StateMachine\Service;

use Doctrine\ORM\EntityManagerInterface;
use ElityDEV\StateMachine\Domain\DTO\TransitionData;
use ElityDEV\StateMachine\Domain\Enum\Transition;
use ElityDEV\StateMachine\Domain\Exception\StateMachineException;
use ElityDEV\StateMachine\Domain\Stateful;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class PersistenceAwareTransitionApplier
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StateMachineResolver   $stateMachineResolver,
    )
    {
    }

    /** @throws StateMachineException|Throwable */
    public function applyTransition(Stateful $object, Transition|string $transition, ?string $stateMachineClass = null): Stateful
    {
        $stateMachine = $this->stateMachineResolver->resolveStateMachine(
            $stateMachineClass ?? array_values($object->getStateMachineClasses())[0]
        );

        if (!$transition instanceof Transition) {
            $transition = $stateMachine->transitionsClass::tryFrom($transition);
            !$transition && throw new BadRequestException('Transition not found.');
        }

        $this->entityManager->beginTransaction();

        try {
            $stateMachine->applyTransition($object, $transition);

            $this->entityManager->persist($object);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $throwable) {
            $this->entityManager->rollback();

            throw $throwable;
        }

        return $object;
    }

    /** @throws Exception */
    public function can(Stateful $object, Transition $transition, ?string $stateMachineClass = null, bool $soft = true): bool
    {
        $stateMachine = $this->stateMachineResolver->resolveStateMachine(
            $stateMachineClass ?? array_values($object->getStateMachineClasses())[0]
        );

        return $stateMachine->can(new TransitionData($object, $transition, $stateMachine->getState($object)), $soft);
    }
}
