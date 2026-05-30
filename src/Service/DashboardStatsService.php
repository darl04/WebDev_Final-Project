<?php

namespace App\Service;

use App\Entity\Orders;
use App\Repository\CustomerRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\RentalRepository;

final class DashboardStatsService
{
    public function __construct(
        private readonly ProductsRepository $productsRepository,
        private readonly CustomerRepository $customerRepository,
        private readonly OrdersRepository $ordersRepository,
        private readonly RentalRepository $rentalRepository,
    ) {
    }

    /**
     * @return array{
     *     totalProducts: int,
     *     totalCustomers: int,
     *     totalOrders: int,
     *     revenue: float,
     *     orderRevenue: float,
     *     rentalRevenue: float,
     *     recentOrders: list<array<string, mixed>>
     * }
     */
    public function getStats(int $recentLimit = 10): array
    {
        $totalProducts = count($this->productsRepository->findAll());
        $totalCustomers = count($this->customerRepository->findAll());
        $orders = $this->ordersRepository->findAll();
        $totalOrders = count($orders);

        $orderRevenue = 0.0;
        foreach ($orders as $order) {
            if ($order->getStatus() !== 'Pending') {
                foreach ($order->getProducts() as $product) {
                    $orderRevenue += (float) ($product->getPrice() ?? 0) * (int) ($order->getQuantity() ?? 1);
                }
            }
        }

        $rentalRevenue = 0.0;
        foreach ($this->rentalRepository->findAll() as $rental) {
            if ($rental->getStatus() !== 'Cancelled' && $rental->getRentalPrice() !== null) {
                $rentalRevenue += (float) $rental->getRentalPrice();
            }
        }

        $recent = $this->ordersRepository->findRecent($recentLimit);

        return [
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'revenue' => $orderRevenue + $rentalRevenue,
            'orderRevenue' => $orderRevenue,
            'rentalRevenue' => $rentalRevenue,
            'recentOrders' => array_map(
                fn (Orders $order) => $this->serializeRecentOrder($order),
                $recent,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRecentOrder(Orders $order): array
    {
        $productNames = [];
        foreach ($order->getProducts() as $product) {
            $productNames[] = $product->getName();
        }

        $createdAt = $order->getCreatedAt();

        return [
            'id' => $order->getId(),
            'status' => $order->getStatus(),
            'statusClass' => strtolower(str_replace(' ', '-', (string) $order->getStatus())),
            'customerName' => $order->getCustomer()?->getName() ?? 'N/A',
            'productNames' => $productNames,
            'itemCount' => $order->getProducts()->count(),
            'quantity' => $order->getQuantity(),
            'createdAt' => $createdAt?->format('M j, Y g:i A'),
            'showUrl' => null,
        ];
    }
}
