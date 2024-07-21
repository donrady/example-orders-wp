<?php

namespace ElityDEV\StateMachine\Service;

use ElityDEV\StateMachine\Domain\Stateful;
use ElityDEV\StateMachine\BaseStateMachine;
use ElityDEV\StateMachine\Domain\DTO\TransitionData;
use Exception;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ReverseContainer;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

readonly class StateMachineResolver
{
    public function __construct(
        private ReverseContainer $parameterBag,
    ) {
    }

    /** @throws Exception */
    public function resolveStateMachine(string $stateMachineClass): BaseStateMachine
    {
        try {
            /** @var BaseStateMachine $stateMachine */
            $stateMachine = $this->parameterBag->getService($stateMachineClass);

            !($stateMachine instanceof BaseStateMachine)
            && throw new Exception('State machine is not instance of BaseStateMachine.');
        } catch (ServiceNotFoundException) {
            throw new BadRequestException('State machine is not defined in container.');
        }

        return $stateMachine;
    }

    /**
     * @return array<string, array<string, string|true>>
     * @throws Exception
     */
    public function resolveAvailableTransitions(Stateful $object): array
    {
        $output = [];

        foreach ($object->getStateMachineClasses() as $stateMachineClass) {
            $stateMachine = $this->resolveStateMachine($stateMachineClass);

            $transitionsOutput = [];

            foreach ($stateMachine->getTransitions() as $transition) {
                $transitionsOutput[$transition->value] = $stateMachine->can(new TransitionData($object, $transition, $stateMachine->getState($object)), true);
            }

            $output[$stateMachine->propertyPath] = $transitionsOutput;
        }

        return $output;
    }
}
