<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Appointment;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\Target;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user = Auth::user();
        if (!$user) {
            $this->redirect('login');
        }

        if ($user['role'] === 'manager') {
            $this->managerDashboard($user);
            return;
        }

        $this->sellerDashboard($user);
    }

    private function sellerDashboard(array $user): void
    {
        $today = date('Y-m-d');
        [$weekStart, $weekEnd] = $this->weekRange($today);
        [$monthStart, $monthEnd] = $this->monthRange($today);

        $metrics = [
            'appointments' => [
                'today' => Appointment::countBetween($user, $today, $today),
                'week' => Appointment::countBetween($user, $weekStart, $weekEnd),
                'month' => Appointment::countBetween($user, $monthStart, $monthEnd),
            ],
            'quotes' => [
                'today' => Quote::countBetween($user, $today, $today),
                'week' => Quote::countBetween($user, $weekStart, $weekEnd),
                'month' => Quote::countBetween($user, $monthStart, $monthEnd),
            ],
            'sales' => [
                'today' => Sale::countBetween($user, $today, $today),
                'week' => Sale::countBetween($user, $weekStart, $weekEnd),
                'month' => Sale::countBetween($user, $monthStart, $monthEnd),
            ],
        ];

        $targets = [
            'weekly' => [
                'appointments' => Target::currentForDate((int) $user['id'], 'weekly', 'appointments', $today),
                'quotes' => Target::currentForDate((int) $user['id'], 'weekly', 'quotes', $today),
                'sales' => Target::currentForDate((int) $user['id'], 'weekly', 'sales', $today),
            ],
            'monthly' => [
                'appointments' => Target::currentForDate((int) $user['id'], 'monthly', 'appointments', $today),
                'quotes' => Target::currentForDate((int) $user['id'], 'monthly', 'quotes', $today),
                'sales' => Target::currentForDate((int) $user['id'], 'monthly', 'sales', $today),
            ],
        ];

        $chartFrom = date('Y-m-d', strtotime('-29 days'));
        $chartTo = $today;
        $apptMap = Appointment::dailyCounts((int) $user['id'], $chartFrom, $chartTo);
        $quoteMap = Quote::dailyCounts((int) $user['id'], $chartFrom, $chartTo);
        $saleMap = Sale::dailyCounts((int) $user['id'], $chartFrom, $chartTo);
        [$labels, $apptSeries, $quoteSeries, $saleSeries] = $this->buildDailySeries($chartFrom, $chartTo, $apptMap, $quoteMap, $saleMap);

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'mode' => 'seller',
            'metrics' => $metrics,
            'targets' => $targets,
            'labels' => $labels,
            'apptSeries' => $apptSeries,
            'quoteSeries' => $quoteSeries,
            'saleSeries' => $saleSeries,
            'periodLabel' => 'Ultimi 30 giorni',
        ]);
    }

    private function managerDashboard(array $user): void
    {
        $period = (string) $this->get('period', 'monthly');
        $today = date('Y-m-d');

        if ($period === 'weekly') {
            [$periodStart, $periodEnd] = $this->weekRange($today);
        } else {
            $period = 'monthly';
            [$periodStart, $periodEnd] = $this->monthRange($today);
        }

        $virtualManager = ['role' => 'manager', 'id' => $user['id']];
        $summary = [
            'appointments' => Appointment::countBetween($virtualManager, $periodStart, $periodEnd),
            'quotes' => Quote::countBetween($virtualManager, $periodStart, $periodEnd),
            'sales' => Sale::countBetween($virtualManager, $periodStart, $periodEnd),
        ];

        $sellers = array_filter(User::all(), static fn(array $u): bool => $u['role'] === 'seller');
        $sellerRows = [];
        foreach ($sellers as $seller) {
            $sellerUser = ['role' => 'seller', 'id' => (int) $seller['id']];
            $sellerRows[] = [
                'seller' => $seller,
                'appointments' => [
                    'actual' => Appointment::countBetween($sellerUser, $periodStart, $periodEnd),
                    'target' => Target::currentForDate((int) $seller['id'], $period, 'appointments', $today)['target_value'] ?? 0,
                ],
                'quotes' => [
                    'actual' => Quote::countBetween($sellerUser, $periodStart, $periodEnd),
                    'target' => Target::currentForDate((int) $seller['id'], $period, 'quotes', $today)['target_value'] ?? 0,
                ],
                'sales' => [
                    'actual' => Sale::countBetween($sellerUser, $periodStart, $periodEnd),
                    'target' => Target::currentForDate((int) $seller['id'], $period, 'sales', $today)['target_value'] ?? 0,
                ],
            ];
        }

        $chartFrom = date('Y-m-d', strtotime('-6 days'));
        $chartTo = $today;
        [$labels, $apptSeries, $quoteSeries, $saleSeries] = $this->managerDailySeries($chartFrom, $chartTo);

        $this->view('dashboard/index', [
            'title' => 'Dashboard Manager',
            'mode' => 'manager',
            'period' => $period,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'summary' => $summary,
            'sellerRows' => $sellerRows,
            'labels' => $labels,
            'apptSeries' => $apptSeries,
            'quoteSeries' => $quoteSeries,
            'saleSeries' => $saleSeries,
            'periodLabel' => 'Ultimi 7 giorni (totale team)',
        ]);
    }

    private function weekRange(string $date): array
    {
        $timestamp = strtotime($date);
        $start = date('Y-m-d', strtotime('monday this week', $timestamp));
        $end = date('Y-m-d', strtotime('sunday this week', $timestamp));
        return [$start, $end];
    }

    private function monthRange(string $date): array
    {
        $timestamp = strtotime($date);
        $start = date('Y-m-01', $timestamp);
        $end = date('Y-m-t', $timestamp);
        return [$start, $end];
    }

    private function buildDailySeries(string $from, string $to, array $apptMap, array $quoteMap, array $saleMap): array
    {
        $labels = [];
        $apptSeries = [];
        $quoteSeries = [];
        $saleSeries = [];
        $cursor = strtotime($from);
        $end = strtotime($to);

        while ($cursor <= $end) {
            $day = date('Y-m-d', $cursor);
            $labels[] = date('d/m', $cursor);
            $apptSeries[] = $apptMap[$day] ?? 0;
            $quoteSeries[] = $quoteMap[$day] ?? 0;
            $saleSeries[] = $saleMap[$day] ?? 0;
            $cursor = strtotime('+1 day', $cursor);
        }

        return [$labels, $apptSeries, $quoteSeries, $saleSeries];
    }

    private function managerDailySeries(string $from, string $to): array
    {
        $db = Database::connection();

        $fetchMap = static function (string $sql, array $params) use ($db): array {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $map = [];
            foreach ($stmt->fetchAll() as $row) {
                $map[$row['d']] = (int) $row['total'];
            }
            return $map;
        };

        $apptMap = $fetchMap(
            "SELECT DATE(start_at) AS d, COUNT(*) AS total
             FROM appointments
             WHERE start_at BETWEEN :from_date AND :to_date
             GROUP BY DATE(start_at)",
            ['from_date' => $from . ' 00:00:00', 'to_date' => $to . ' 23:59:59']
        );
        $quoteMap = $fetchMap(
            "SELECT DATE(created_at) AS d, COUNT(*) AS total
             FROM quotes
             WHERE created_at BETWEEN :from_date AND :to_date
             GROUP BY DATE(created_at)",
            ['from_date' => $from . ' 00:00:00', 'to_date' => $to . ' 23:59:59']
        );
        $saleMap = $fetchMap(
            "SELECT closed_at AS d, COUNT(*) AS total
             FROM sales
             WHERE closed_at BETWEEN :from_date AND :to_date
             GROUP BY closed_at",
            ['from_date' => $from, 'to_date' => $to]
        );

        return $this->buildDailySeries($from, $to, $apptMap, $quoteMap, $saleMap);
    }
}

