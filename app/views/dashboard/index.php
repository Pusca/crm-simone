<?php
$metricLabel = [
    'appointments' => 'Appuntamenti',
    'quotes' => 'Preventivi',
    'sales' => 'Vendite',
];
$progress = static function (int $actual, int $target): int {
    if ($target <= 0) {
        return $actual > 0 ? 100 : 0;
    }
    return min(100, (int) round(($actual / $target) * 100));
};
?>

<?php if (($mode ?? '') === 'seller'): ?>
    <div class="grid gap-4 md:grid-cols-3">
        <?php foreach (['appointments', 'quotes', 'sales'] as $metric): ?>
            <?php $weekTarget = (int) (($targets['weekly'][$metric]['target_value'] ?? 0)); ?>
            <?php $monthTarget = (int) (($targets['monthly'][$metric]['target_value'] ?? 0)); ?>
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?= e($metricLabel[$metric]) ?></h2>
                <div class="mt-3 space-y-2 text-sm">
                    <p><span class="font-medium">Oggi:</span> <?= e((string) $metrics[$metric]['today']) ?></p>
                    <p><span class="font-medium">Settimana:</span> <?= e((string) $metrics[$metric]['week']) ?> / <?= e((string) $weekTarget) ?></p>
                    <div class="h-2 rounded bg-slate-200">
                        <div class="h-2 rounded bg-emerald-500" style="width: <?= e((string) $progress($metrics[$metric]['week'], $weekTarget)) ?>%"></div>
                    </div>
                    <p><span class="font-medium">Mese:</span> <?= e((string) $metrics[$metric]['month']) ?> / <?= e((string) $monthTarget) ?></p>
                    <div class="h-2 rounded bg-slate-200">
                        <div class="h-2 rounded bg-sky-500" style="width: <?= e((string) $progress($metrics[$metric]['month'], $monthTarget)) ?>%"></div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <form method="get" action="<?= e(base_url('dashboard')) ?>" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Periodo KPI</label>
            <select name="period" class="rounded border border-slate-300 px-3 py-2">
                <option value="weekly" <?= selected($period ?? 'monthly', 'weekly') ?>>Settimanale</option>
                <option value="monthly" <?= selected($period ?? 'monthly', 'monthly') ?>>Mensile</option>
            </select>
        </div>
        <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit">Applica</button>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Appuntamenti</h2>
            <p class="mt-3 text-3xl font-bold text-slate-800"><?= e((string) $summary['appointments']) ?></p>
            <p class="text-xs text-slate-500"><?= e($periodStart) ?> - <?= e($periodEnd) ?></p>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Preventivi</h2>
            <p class="mt-3 text-3xl font-bold text-slate-800"><?= e((string) $summary['quotes']) ?></p>
            <p class="text-xs text-slate-500"><?= e($periodStart) ?> - <?= e($periodEnd) ?></p>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Vendite</h2>
            <p class="mt-3 text-3xl font-bold text-slate-800"><?= e((string) $summary['sales']) ?></p>
            <p class="text-xs text-slate-500"><?= e($periodStart) ?> - <?= e($periodEnd) ?></p>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">KPI per venditore</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b bg-slate-50 text-left">
                    <th class="px-3 py-2">Venditore</th>
                    <th class="px-3 py-2">Appuntamenti</th>
                    <th class="px-3 py-2">Preventivi</th>
                    <th class="px-3 py-2">Vendite</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($sellerRows ?? []) as $row): ?>
                    <tr class="border-b">
                        <td class="px-3 py-2 font-medium"><?= e($row['seller']['name']) ?></td>
                        <td class="px-3 py-2"><?= e((string) $row['appointments']['actual']) ?> / <?= e((string) $row['appointments']['target']) ?></td>
                        <td class="px-3 py-2"><?= e((string) $row['quotes']['actual']) ?> / <?= e((string) $row['quotes']['target']) ?></td>
                        <td class="px-3 py-2"><?= e((string) $row['sales']['actual']) ?> / <?= e((string) $row['sales']['target']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="mb-1 text-lg font-semibold">Progressione giornaliera</h2>
    <p class="mb-4 text-sm text-slate-500"><?= e($periodLabel ?? '') ?></p>
    <div class="h-80">
        <canvas id="crmTrendChart"></canvas>
    </div>
</section>

<script>
    const labels = <?= json_encode($labels ?? [], JSON_THROW_ON_ERROR) ?>;
    const apptData = <?= json_encode($apptSeries ?? [], JSON_THROW_ON_ERROR) ?>;
    const quoteData = <?= json_encode($quoteSeries ?? [], JSON_THROW_ON_ERROR) ?>;
    const saleData = <?= json_encode($saleSeries ?? [], JSON_THROW_ON_ERROR) ?>;
    new Chart(document.getElementById('crmTrendChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {label: 'Appuntamenti', data: apptData, borderColor: '#0284c7', backgroundColor: 'rgba(2,132,199,0.1)', tension: 0.25},
                {label: 'Preventivi', data: quoteData, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.1)', tension: 0.25},
                {label: 'Vendite', data: saleData, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.1)', tension: 0.25}
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {legend: {position: 'top'}},
            scales: {y: {beginAtZero: true, ticks: {precision: 0}}}
        }
    });
</script>

