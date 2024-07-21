<?php

namespace ElityDEV\StateMachine\Domain\DTO;

use ElityDEV\StateMachine\Domain\Enum\State;
use ElityDEV\StateMachine\Domain\Enum\Transition;
use ElityDEV\StateMachine\Domain\Stateful;

readonly class TransitionData
{
    public function __construct(
        public Stateful   $object,
        public Transition $transition,
        public State      $fromState,
    )
    {
    }
}
