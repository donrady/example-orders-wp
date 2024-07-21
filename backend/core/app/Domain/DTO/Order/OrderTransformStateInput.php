<?php
declare(strict_types=1);

namespace App\Domain\DTO\Order;

use App\Domain\DTO\TransformStateInput;
use App\Domain\Enum\Order\OrderStateTransition;
use App\StateMachine\OrderStateMachine;

final readonly class OrderTransformStateInput implements TransformStateInput
{
    public function __construct(
        private OrderStateTransition $transition
    )
    {
    }

    public function getTransition(): OrderStateTransition
    {
        return $this->transition;
    }

    public function getStateMachineClass(): string
    {
        return OrderStateMachine::class;
    }
}
