<div class="no-print mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="<?= e(base_url('activities')) ?>" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Tipo</label>
            <select name="type" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <?php foreach (['call', 'email', 'whatsapp', 'meeting', 'other'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= selected($filters['type'] ?? '', $type) ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Pipeline</label>
            <select name="stage_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= e((string) $stage['id']) ?>" <?= selected($filters['stage_id'] ?? '', $stage['id']) ?>><?= e($stage['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Cliente</label>
            <select name="client_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= e((string) $client['id']) ?>" <?= selected($filters['client_id'] ?? '', $client['id']) ?>><?= e($client['company_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Da</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="rounded border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">A</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="rounded border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700" type="submit">Filtra</button>
    </form>
    <a href="<?= e(base_url('activities/create')) ?>" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">+ Nuova Attività</a>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="border-b bg-slate-50 text-left">
            <th class="px-3 py-2">Data/Ora</th>
            <th class="px-3 py-2">Tipo</th>
            <th class="px-3 py-2">Cliente</th>
            <th class="px-3 py-2">Stage</th>
            <th class="px-3 py-2">Oggetto</th>
            <th class="px-3 py-2">Operatore</th>
            <th class="no-print px-3 py-2">Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($activities as $activity): ?>
            <tr class="border-b">
                <td class="px-3 py-2"><?= e($activity['occurred_at']) ?></td>
                <td class="px-3 py-2 uppercase"><?= e($activity['type']) ?></td>
                <td class="px-3 py-2"><?= e($activity['company_name']) ?></td>
                <td class="px-3 py-2"><?= e($activity['stage_name']) ?></td>
                <td class="no-print px-3 py-2">
                    <div class="font-medium"><?= e($activity['subject']) ?></div>
                    <div class="text-xs text-slate-500"><?= e($activity['body']) ?></div>
                </td>
                <td class="px-3 py-2"><?= e($activity['seller_name']) ?></td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap gap-1">
                        <a class="rounded bg-amber-100 px-2 py-1 text-amber-700 hover:bg-amber-200" href="<?= e(base_url('quotes/create?activity_id=' . $activity['id'])) ?>">Crea preventivo</a>
                        <a class="rounded bg-sky-100 px-2 py-1 text-sky-700 hover:bg-sky-200" href="<?= e(base_url('activities/' . $activity['id'] . '/edit')) ?>">Modifica</a>
                        <form method="post" action="<?= e(base_url('activities/' . $activity['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminare attività?');">
                            <?= csrf_field() ?>
                            <button class="rounded bg-red-100 px-2 py-1 text-red-700 hover:bg-red-200" type="submit">Elimina</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
