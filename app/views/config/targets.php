<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold">Wizard target venditore</h2>
    <form method="post" action="<?= e(base_url('config/targets')) ?>" class="grid gap-3 md:grid-cols-3">
        <?= csrf_field() ?>
        <div>
            <label class="mb-1 block text-sm font-medium">Venditore *</label>
            <select name="user_id" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Seleziona</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= e((string) $user['id']) ?>"><?= e($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Periodo *</label>
            <select name="period" class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="weekly">Settimanale</option>
                <option value="monthly">Mensile</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Start date *</label>
            <input type="date" name="start_date" value="<?= e(date('Y-m-d')) ?>" required class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">End date *</label>
            <input type="date" name="end_date" value="<?= e(date('Y-m-t')) ?>" required class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Target appuntamenti</label>
            <input type="number" min="0" name="appointments_target" value="10" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Target preventivi</label>
            <input type="number" min="0" name="quotes_target" value="5" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Target vendite</label>
            <input type="number" min="0" name="sales_target" value="3" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-3">
            <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit">Salva target</button>
        </div>
    </form>
</section>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="get" action="<?= e(base_url('config/targets')) ?>" class="mb-3 flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Venditore</label>
            <select name="user_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= e((string) $user['id']) ?>" <?= selected($filters['user_id'] ?? '', $user['id']) ?>><?= e($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Periodo</label>
            <select name="period" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <option value="weekly" <?= selected($filters['period'] ?? '', 'weekly') ?>>Settimanale</option>
                <option value="monthly" <?= selected($filters['period'] ?? '', 'monthly') ?>>Mensile</option>
            </select>
        </div>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700" type="submit">Filtra</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="border-b bg-slate-50 text-left">
                <th class="px-3 py-2">Venditore</th>
                <th class="px-3 py-2">Periodo</th>
                <th class="px-3 py-2">Metrica</th>
                <th class="px-3 py-2">Target</th>
                <th class="px-3 py-2">Range</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($targets as $target): ?>
                <tr class="border-b">
                    <td class="px-3 py-2"><?= e($target['seller_name']) ?></td>
                    <td class="px-3 py-2"><?= e($target['period']) ?></td>
                    <td class="px-3 py-2"><?= e($target['metric']) ?></td>
                    <td class="px-3 py-2"><?= e((string) $target['target_value']) ?></td>
                    <td class="px-3 py-2"><?= e($target['start_date']) ?> → <?= e($target['end_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

