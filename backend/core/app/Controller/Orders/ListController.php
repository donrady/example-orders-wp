<?php
declare(strict_types=1);

namespace App\Controller\Orders;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ListController extends AbstractController
{

    public function indexAction()
    {
        return $this->json(['message' => 'Hello from Orders List Controller!']);
    }
}
