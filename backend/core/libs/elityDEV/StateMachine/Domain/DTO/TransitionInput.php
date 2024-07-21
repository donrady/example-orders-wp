<?php

namespace ElityDEV\StateMachine\Domain\DTO;

readonly class TransitionInput
{
    public function __construct(
        public string  $iri,
        public string  $transition,
        public ?string $stateName = null,
    ) {
    }
}
