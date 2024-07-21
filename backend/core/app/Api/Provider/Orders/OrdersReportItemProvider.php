<?php
declare(strict_types=1);

namespace App\Api\Provider\Orders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Resource\Order\OrdersReport;
use App\Entity\Order\Order;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class OrdersReportItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): OrdersReport
    {
        $query = $this->entityManager->createQuery('SELECT o FROM App\Entity\Order\Order o WHERE o.createdAt BETWEEN :from AND :to');
        [$from, $to] = explode('_', $uriVariables['from_to']);
        $query->setParameter('from', DateTime::createFromFormat('Y-m-d H:i:s', "$from 00:00:00"));
        $query->setParameter('to', DateTime::createFromFormat('Y-m-d H:i:s', "$to 23:59:59"));

        $orders = $query->getResult();

        $totalOrders = count($orders);
        $totalAmount = array_reduce(
            $orders,
            fn($carry, Order $order) => $carry + (int)$order->getTotalAmount()->getAmount(),
            0,
        );

        return new OrdersReport($from, $to, $totalOrders, $totalAmount);
    }
}
