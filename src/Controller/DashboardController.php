<?php

namespace App\Controller;

use App\Service\DashboardStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DashboardStatsService $dashboardStatsService): Response
    {
        $stats = $dashboardStatsService->getStats();

        return $this->render('dashboard/index.html.twig', [
            'totalProducts' => $stats['totalProducts'],
            'totalCustomers' => $stats['totalCustomers'],
            'totalOrders' => $stats['totalOrders'],
            'revenue' => $stats['revenue'],
            'orderRevenue' => $stats['orderRevenue'],
            'rentalRevenue' => $stats['rentalRevenue'],
            'recentOrders' => $stats['recentOrders'],
        ]);
    }

    #[Route('/dashboard/poll', name: 'app_dashboard_poll', methods: ['GET'])]
    public function poll(DashboardStatsService $dashboardStatsService): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw $this->createAccessDeniedException();
        }

        $stats = $dashboardStatsService->getStats();

        foreach ($stats['recentOrders'] as &$order) {
            $order['showUrl'] = $this->generateUrl('app_orders_show', ['id' => $order['id']]);
        }
        unset($order);

        return $this->json($stats);
    }
}
