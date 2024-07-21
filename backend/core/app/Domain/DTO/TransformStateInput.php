<?php
declare(strict_types=1);

namespace App\Domain\DTO;

use ElityDEV\StateMachine\Domain\Enum\Transition;

interface TransformStateInput
{
    public function getTransition(): Transition;

    /**
     * @return class-string
     */
    public function getStateMachineClass(): string;
}
