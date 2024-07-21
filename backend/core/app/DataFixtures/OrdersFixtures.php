<?php

namespace App\DataFixtures;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Money\Money;

class OrdersFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $order = new Order((new DateTime())->format("ymd-$i"));
            $orderTotalAmount = Money::USD(0);
            for ($j = 0; $j < rand(1, 5); $j++) {
                $item = new OrderItem($order, "Item $j", Money::USD(rand(1000, 10000)));
                $order->addItem($item);
                $orderTotalAmount = $orderTotalAmount->add($item->getUnitPrice()->multiply($item->getQuantity()));
            }
            $order->setTotalAmount($orderTotalAmount);

            $manager->persist($order);
        }

        $manager->flush();
    }
}
