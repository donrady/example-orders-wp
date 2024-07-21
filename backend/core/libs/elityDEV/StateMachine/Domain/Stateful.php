<?php

namespace ElityDEV\StateMachine\Domain;

interface Stateful
{
    /** @return array<string, class-string> */
    public function getStateMachineClasses(): array;
}
