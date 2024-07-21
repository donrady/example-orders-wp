<?php
declare(strict_types=1);

namespace App\Entity\Order;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Api\Processor\TransformStateProcessor;
use App\Domain\DTO\Order\OrderTransformStateInput;
use App\Domain\Enum\Order\OrderState;
use App\Entity\Columns;
use App\StateMachine\OrderStateMachine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ElityDEV\StateMachine\Domain\Stateful;
use ElityDEV\Utils\Money\Bridge\Doctrine\MoneyIntegrated;
use Money\Money;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[
    ApiResource(
        operations: [
            new GetCollection(
                normalizationContext: [
                    AbstractNormalizer::GROUPS => [
                        Columns\ColumnsGroups::ALL,
                        self::GROUP_READ_COLLECTION,
                    ],
                ],
            ),
            new Get(),
            new Patch(
                uriTemplate: '/orders/{id}/transform-state',
                input: OrderTransformStateInput::class,
                name: self::PATCH_TRANSFORM_STATE,
                processor: TransformStateProcessor::class,
            )
        ],
        normalizationContext: [
            AbstractNormalizer::GROUPS => [
                Columns\ColumnsGroups::ALL,
                self::GROUP_READ_ITEM,
                OrderItem::GROUP_READ,
            ],
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
        ]
    ),
    ORM\Entity,
    ORM\Table(name: 'order_orders'),
    ORM\HasLifecycleCallbacks,
]
class Order implements MoneyIntegrated, Stateful
{
    use Columns\Identified, Columns\CreatedAt, Columns\UpdatedAt;

    public const string GROUP_READ_COLLECTION = 'app:entity:order:order:read:collection';
    public const string GROUP_READ_ITEM = 'app:entity:order:order:read:item';

    public const string PATCH_TRANSFORM_STATE = 'app:entity:order:order:patch-transform-state';

    # Requested from the client, but we can use it for human-readable order ID
    #[Groups([self::GROUP_READ_COLLECTION, self::GROUP_READ_ITEM])]
    #[ORM\Column]
    private string $name;

    #[Groups([self::GROUP_READ_COLLECTION, self::GROUP_READ_ITEM])]
    #[ORM\Column(enumType: OrderState::class)]
    private OrderState $state = OrderState::NEW;

    #[Groups([self::GROUP_READ_COLLECTION, self::GROUP_READ_ITEM])]
    #[ORM\Embedded]
    private Money $totalAmount;

    #[MaxDepth(1)]
    #[Groups([self::GROUP_READ_ITEM])]
    #[ApiProperty(readableLink: true)]
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->items = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getState(): OrderState
    {
        return $this->state;
    }

    public function setState(OrderState $state): void
    {
        $this->state = $state;
    }

    public function getTotalAmount(): Money
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(Money $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        return $this;
    }

    public function removeOrderItem(OrderItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    public function getStateMachineClasses(): array
    {
        return [
            'state' => OrderStateMachine::class,
        ];
    }
}
