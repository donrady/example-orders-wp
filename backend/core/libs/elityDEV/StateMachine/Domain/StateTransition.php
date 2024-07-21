<?php

namespace ElityDEV\StateMachine\Domain;



use ElityDEV\StateMachine\Domain\Enum\State;
use ElityDEV\StateMachine\Domain\Enum\Transition;

class StateTransition
{
    /**
     * @param Transition $name
     * @param State[] $from
     * @param State $to
     * @param callable[] $guards
     * @param callable[] $afterCallbacks
     * @param callable[] $beforeCallbacks
     */
    public function __construct(
        public Transition $name,
        public array      $from,
        public State      $to,
        public array      $guards = [],
        public array      $afterCallbacks = [],
        public array      $beforeCallbacks = [],
    ) {
    }
}
