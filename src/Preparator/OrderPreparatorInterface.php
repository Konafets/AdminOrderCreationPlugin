<?php

declare(strict_types=1);

namespace Sylius\AdminOrderCreationPlugin\Preparator;

use Sylius\AdminOrderCreationPlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\Request;

interface OrderPreparatorInterface
{
    public function prepareFromRequest(Request $request): OrderInterface;
}
