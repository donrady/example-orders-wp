<?php

namespace ElityDEV\StateMachine\Domain\Exception;

use ElityDEV\StateMachine\Domain\Enum\State;
use ElityDEV\StateMachine\Domain\Enum\Transition;
use Exception;

final class StateMachineException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(
        public $message,
        public string     $stateMachineClass,
        public Transition $transition,
        public State $currentState,
        public $code = 0,
    ) {
        parent::__construct($this->message, $this->code);
    }
}
