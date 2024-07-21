<?php
declare(strict_types=1);

namespace App\Tests\Api\Orders;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Domain\Enum\Order\OrderState;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Tests\Api\BaseApiTestCase;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Money\Money;
use Zenstruck\Foundry\Test\ResetDatabase;


class OrderGetOneTest extends BaseApiTestCase
{
    use ResetDatabase;

    protected EntityManagerInterface $entityManager;

    private Client $client;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = $this->getEntityManager();
        $this->client = self::createClient();
    }

    public function testOrderGetSuccess()
    {
        $order = new Order((new DateTime())->format("ymd-1"));
        $order->addItem(
            new OrderItem($order, 'Order item', Money::USD(1000))
        );
        $order->setTotalAmount(Money::USD(1000));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->client->request('GET', "/orders/{$order->getId()}");

        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            'name' => (new DateTime())->format("ymd-1"),
            'state' => OrderState::NEW->value,
            'totalAmount' => [
                'amount' => 10.00,
                'currency' => 'USD',
            ],
        ], false);
    }
}
