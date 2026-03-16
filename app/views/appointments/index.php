<?php
$prevWeek = date('Y-m-d', strtotime($weekStart . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($weekStart . ' +7 days'));
?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="<?= e(base_url('appointments?week=' . $prevWeek)) ?>" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">← Settimana prec.</a>
        <a href="<?= e(base_url('appointments?week=' . date('Y-m-d'))) ?>" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Settimana corrente</a>
        <a href="<?= e(base_url('appointments?week=' . $nextWeek)) ?>" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Sett. succ. →</a>
    </div>
    <div class="flex items-center gap-2">
        <p class="text-sm text-slate-600"><?= e($weekStart) ?> - <?= e($weekEnd) ?></p>
        <a href="<?= e(base_url('appointments/create')) ?>" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">+ Nuovo Appuntamento</a>
    </div>
</div>

<section class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-xs">
        <thead>
        <tr class="border-b bg-slate-50">
            <th class="w-20 px-2 py-2 text-left">Ora</th>
            <?php foreach ($days as $day): ?>
                <th class="min-w-40 px-2 py-2 text-left"><?= e($day['label']) ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($hours as $hour): ?>
            <tr class="border-b align-top">
                <td class="border-r bg-slate-50 px-2 py-2 font-medium"><?= e($hour) ?></td>
                <?php foreach ($days as $day): ?>
                    <?php $items = $groupedAppointments[$day['date']][$hour] ?? []; ?>
                    <td class="h-20 border-r px-2 py-2">
                        <?php foreach ($items as $item): ?>
                            <div class="mb-1 rounded border border-sky-200 bg-sky-50 p-2">
                                <div class="font-medium text-sky-800"><?= e(substr($item['start_at'], 11, 5)) ?>-<?= e(substr($item['end_at'], 11, 5)) ?> <?= e($item['title']) ?></div>
                                <div class="text-slate-700"><?= e($item['company_name']) ?></div>
                                <div class="text-slate-500"><?= e($item['location']) ?></div>
                                <a class="text-xs text-sky-700 underline" href="<?= e(base_url('appointments/' . $item['id'] . '/edit')) ?>">Modifica</a>
                            </div>
                        <?php endforeach; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="mb-3 text-lg font-semibold">Elenco appuntamenti settimana</h2>
    <div class="space-y-2">
        <?php foreach ($appointments as $appointment): ?>
            <article class="rounded border border-slate-200 p-3 text-sm">
                <p class="font-medium"><?= e($appointment['title']) ?> - <?= e($appointment['company_name']) ?></p>
                <p class="text-slate-600"><?= e($appointment['start_at']) ?> → <?= e($appointment['end_at']) ?> | <?= e($appointment['location']) ?></p>
                <div class="mt-2 flex gap-2">
                    <a class="rounded bg-sky-100 px-2 py-1 text-sky-700 hover:bg-sky-200" href="<?= e(base_url('appointments/' . $appointment['id'] . '/edit')) ?>">Modifica</a>
                    <form method="post" action="<?= e(base_url('appointments/' . $appointment['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminare appuntamento?');">
                        <?= csrf_field() ?>
                        <button class="rounded bg-red-100 px-2 py-1 text-red-700 hover:bg-red-200" type="submit">Elimina</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (count($appointments) === 0): ?>
            <p class="rounded border border-dashed border-slate-300 p-4 text-sm text-slate-500">Nessun appuntamento in questa settimana.</p>
        <?php endif; ?>
    </div>
</section>

