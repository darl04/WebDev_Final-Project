<?php

namespace App\Service;

use App\Entity\Orders;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OrderPollSerializer
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeForCustomer(Orders $order): array
    {
        $status = (string) $order->getStatus();
        $statusKey = strtolower($status);
        $statusClass = str_replace(' ', '-', $statusKey);
        $createdAt = $order->getCreatedAt();

        return [
            'id' => $order->getId(),
            'status' => $status,
            'statusKey' => $statusKey,
            'statusClass' => $statusClass,
            'title' => $this->statusTitle($statusKey),
            'iconClass' => $this->statusIconClass($statusKey),
            'iconHtml' => $this->statusIconHtml($statusKey),
            'createdAt' => $createdAt?->format('M j, Y g:i A'),
            'itemCount' => $order->getProducts()->count(),
            'showUrl' => $this->urlGenerator->generate('app_my_orders_show', ['id' => $order->getId()]),
        ];
    }

    private function statusTitle(string $statusKey): string
    {
        return match ($statusKey) {
            'delivered' => 'Your order has been delivered',
            'cancelled' => 'This order was cancelled',
            default => 'Your order is pending',
        };
    }

    private function statusIconClass(string $statusKey): string
    {
        return match ($statusKey) {
            'delivered' => 'fas fa-circle-check',
            'cancelled' => 'fas fa-circle-xmark',
            default => 'fas fa-clock',
        };
    }

    private function statusIconHtml(string $statusKey): string
    {
        $class = $this->statusIconClass($statusKey);

        return sprintf('<i class="%s"></i>', $class);
    }
}
