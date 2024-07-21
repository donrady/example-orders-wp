<?php
declare(strict_types=1);

namespace App\Entity\Order;

use ApiPlatform\Action\NotExposedAction;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Domain\Enum\Order\OrderItemUnit;
use App\Entity\Columns;
use Doctrine\ORM\Mapping as ORM;
use ElityDEV\Utils\Money\Bridge\Doctrine\MoneyIntegrated;
use Money\Money;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        operations: [
            new Get(
                controller: NotExposedAction::class,
                openapi: false,
            ),
        ],
    ),
    ORM\Entity,
    ORM\Table(name: 'order_items'),
    ORM\HasLifecycleCallbacks,
]
class OrderItem implements MoneyIntegrated
{
    use Columns\Identified, Columns\CreatedAt, Columns\UpdatedAt;

    public const string GROUP_READ = 'app:entity:order:order-item:read';

    #[Groups([self::GROUP_READ])]
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    private Order $order;

    #[Groups([self::GROUP_READ])]
    #[ORM\Column]
    private string $name;

    #[Groups([self::GROUP_READ])]
    #[ORM\Embedded]
    private Money $unitPrice;

    #[Groups([self::GROUP_READ])]
    #[ORM\Column(enumType: OrderItemUnit::class)]
    private OrderItemUnit $unit = OrderItemUnit::PIECE;

    #[Groups([self::GROUP_READ])]
    #[ORM\Column]
    private int $quantity = 1;

    public function __construct(Order $order, string $name, Money $unitPrice)
    {
        $this->order = $order;
        $this->name = $name;
        $this->unitPrice = $unitPrice;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(Money $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getUnit(): OrderItemUnit
    {
        return $this->unit;
    }

    public function setUnit(OrderItemUnit $unit): void
    {
        $this->unit = $unit;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
