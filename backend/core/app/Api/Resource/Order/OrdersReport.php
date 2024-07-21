<?php
declare(strict_types=1);

namespace App\Api\Resource\Order;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\Provider\Orders\OrdersReportItemProvider;

#[
    ApiResource(
        operations: [
            new Get(
                uriTemplate: '/orders-report/{from_to}',
                uriVariables: [
                    'from_to'
                ],
                requirements: [
                    'from_to' => '\d{4}-\d{2}-\d{2}_\d{4}-\d{2}-\d{2}',
                ],
                provider: OrdersReportItemProvider::class,
            ),
        ],
    ),
]
final readonly class OrdersReport
{
    public function __construct(
        public string $from,
        public string $to,
        public int    $totalOrders,
        public int    $totalAmount,
    )
    {
    }

    #[ApiProperty(identifier: true)]
    public function getFromTo(): string
    {
        return "{$this->from}_$this->to";
    }
}
