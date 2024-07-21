<?php

namespace ElityDEV\StateMachine;

use ElityDEV\StateMachine\Domain\DTO\TransitionData;
use ElityDEV\StateMachine\Domain\Enum\CallbackType;
use ElityDEV\StateMachine\Domain\Enum\State;
use ElityDEV\StateMachine\Domain\Enum\Transition;
use ElityDEV\StateMachine\Domain\Exception\StateMachineException;
use ElityDEV\StateMachine\Domain\Stateful;
use ElityDEV\StateMachine\Domain\StateTransition;
use Exception;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Throwable;

abstract class BaseStateMachine
{

    /**
     * @param string $className Class name of stateful object.
     * @param string $propertyPath Property path to state property in stateful object.
     * @param class-string<Transition> $transitionsClass Class name of enum with transitions for this state machine.
     * @param class-string<State> $statesClass Class name of enum with states for this state machine.
     * @param StateTransition[] $transitionsMap Map of transitions with their states and callbacks.
     * @throws Exception
     */
    public function __construct(
        public readonly string $className,
        public readonly string $propertyPath,
        public readonly string $transitionsClass,
        public readonly string $statesClass,
        /**
         * @example
         *      transitionsMap: [
         *          new StateTransition(
         *              name: ApprovalTransition::APPROVE,
         *              from: [ApprovalTransition::WAITING_FOR_APPROVAL],
         *              to: ApprovalState::APPROVED,
         *              guards: [
         *                  fn(Stateful $object) => $object->isChecked() ?: 'Object has not been checked yet.',
         *              ],
         *              beforeCallbacks: [
         *                  fn(Stateful $object) => $object->setApprovedBy($signedUser),
         *              ],
         *              afterCallbacks: [
         *                  fn(Stateful $object) => $object->setApprovedAt(new DateTime()),
         *              ],
         *          ),
         *      ],
         */
        public readonly array  $transitionsMap = [],
    )
    {
        $this->validateStateMachine();
    }

    /** @return Transition[] */
    public function getTransitions(): array
    {
        return $this->transitionsClass::cases();
    }

    /** @return State[] */
    public function getStates(): array
    {
        return $this->statesClass::cases();
    }

    /** @throws Exception */
    private function validateStateMachine(): void
    {
        !property_exists($this->className, $this->propertyPath)
        && throw new Exception("Property $this->propertyPath does not exist in $this->className.");

        !enum_exists($this->transitionsClass)
        && throw new Exception("Enum $this->transitionsClass does not exist.");

        count(
            array_unique(
                array_map(
                    fn(StateTransition $stateTransition) => $stateTransition->name->value,
                    $this->transitionsMap,
                ),
            ),
        ) !== count($this->getTransitions())
        && throw new Exception('Transitions are not unique or some of them are missing.');

        foreach ($this->transitionsMap as $transition) {
            if (!in_array($transition->to, $this->getStates())
                || array_diff(
                    array_map(fn(State $enum) => $enum->value, $transition->from),
                    array_map(fn(State $enum) => $enum->value, $this->getStates())
                )
            ) {
                throw new Exception("States in transition {$transition->name->value} are not defined in states enum.");
            }
        }
    }

    /** @throws StateMachineException|Exception|Throwable */
    final public function applyTransition(Stateful $object, Transition $transition): void
    {
        $transitionData = $this->createTransitionData($object, $transition);
        $this->validateTransition($transitionData);
        $this->handleCallbacks($transitionData, CallbackType::BEFORE);

        $this->setState($object, $this->getStateTransitionByTransition($transition)->to);

        $this->handleCallbacks($transitionData, CallbackType::AFTER);
    }

    /** @throws Exception */
    final public function can(TransitionData $data, bool $soft = false): true|string
    {
        if (!in_array(
            $data->fromState,
            $this->getStateTransitionByTransition($data->transition)->from,
        )) {
            return "Transition {$data->transition->value} is not allowed for state {$data->fromState->value}";
        }

        if ($soft) {
            try {
                return $this->handleGuardCallbacks($data);
            } catch (Throwable $throwable) {
                return $throwable->getMessage();
            }
        }

        return $this->handleGuardCallbacks($data);
    }

    /** @throws StateMachineException|Exception */
    private function validateTransition(TransitionData $data): void
    {
        $result = $this->can($data);

        if (is_string($result)) {
            throw new StateMachineException(
                message: $result,
                stateMachineClass: static::class,
                transition: $data->transition,
                currentState: $data->fromState,
                code: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }
    }

    /** @throws Exception */
    private function handleGuardCallbacks(TransitionData $data): true|string
    {
        $stateTransition = $this->getStateTransitionByTransition($data->transition);

        foreach ($stateTransition->guards as $guard) {
            $result = $this->resolveCallback($guard, $data->object, $data->fromState);

            if (is_null($result)) {
                return "Guard callback for transition {$data->transition->value} returned null. It must return either [true] or [string] with error message.";
            }

            if (is_string($result)) {
                return $result;
            }
        }

        return true;
    }

    /** @throws Exception */
    private function handleCallbacks(TransitionData $data, CallbackType $type): void
    {
        $stateTransition = $this->getStateTransitionByTransition($data->transition);

        $callbacks = match ($type) {
            CallbackType::BEFORE => $stateTransition->beforeCallbacks,
            CallbackType::AFTER => $stateTransition->afterCallbacks,
            CallbackType::GUARD => throw new Exception('Case implemented in other function.'),
        };

        foreach ($callbacks as $callback) {
            $this->resolveCallback($callback, $data->object, $data->fromState);
        }
    }

    public function getState(Stateful $object): State
    {
        return PropertyAccess::createPropertyAccessor()->getValue($object, $this->propertyPath);
    }

    private function setState(Stateful $object, State $state): void
    {
        PropertyAccess::createPropertyAccessor()->setValue($object, $this->propertyPath, $state);
    }

    /** @throws Exception */
    private function getStateTransitionByTransition(Transition $transition): StateTransition
    {
        $result = array_filter(
            $this->transitionsMap,
            fn(StateTransition $stateTransition) => $stateTransition->name === $transition,
        );

        empty($result) && throw new Exception('Transition not found.');

        return array_values($result)[0];
    }

    /**
     * @throws ReflectionException
     *
     * This function resolves which parameters $callback needs, retrieves them from $args and calls $callback with them.
     */
    private function resolveCallback(callable $callback, Stateful $object, State $fromState): true|string|null
    {
        return $callback(
            $object,
            count(array_filter((new ReflectionFunction($callback))->getParameters(), fn(ReflectionParameter $parameter) => $parameter->name === 'fromState')) > 0 ? $fromState : null,
        );
    }

    private function createTransitionData(Stateful $object, Transition $transition): TransitionData
    {
        return new TransitionData(
            object: $object,
            transition: $transition,
            fromState: $this->getState($object),
        );
    }
}
