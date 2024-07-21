<?php
declare(strict_types=1);

namespace App\StateMachine;

use App\Domain\Enum\Order\OrderState;
use App\Domain\Enum\Order\OrderStateTransition;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use ElityDEV\StateMachine\BaseStateMachine;
use ElityDEV\StateMachine\Domain\StateTransition;
use Money\Currency;
use Money\Money;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure]
class OrderStateMachine extends BaseStateMachine
{
    public function __construct()
    {
        parent::__construct(
            className: Order::class,
            propertyPath: 'state',
            transitionsClass: OrderStateTransition::class,
            statesClass: OrderState::class,
            transitionsMap: [
                new StateTransition(
                    name: OrderStateTransition::CONFIRM,
                    from: [OrderState::NEW, OrderState::WAITING_FOR_CONFIRMATION],
                    to: OrderState::CONFIRMED,
                    guards: [],
                    afterCallbacks: [
                        fn(Order $order) => $order->setTotalAmount(
                            new Money(
                                $order->getItems()->reduce(fn($carry, OrderItem $item) => $carry + (int)$item->getUnitPrice()->getAmount(), 0),
                                new Currency($order->getTotalAmount()->getCurrency()),
                            ),
                        ),
                    ],
                ),
                new StateTransition(
                    name: OrderStateTransition::COMPLETE,
                    from: [OrderState::CONFIRMED],
                    to: OrderState::COMPLETED,
                ),
            ],
        );
    }

}
