<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="<?= e(base_url('quotes')) ?>" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Ricerca</label>
            <input name="q" value="<?= e($filters['q'] ?? '') ?>" class="rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Titolo o cliente">
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Stato</label>
            <select name="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tutti</option>
                <?php foreach (['draft', 'sent', 'won', 'lost'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($filters['status'] ?? '', $status) ?>><?= e($status) ?></option>
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
        <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700">Filtra</button>
    </form>
    <a href="<?= e(base_url('quotes/create')) ?>" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">+ Nuovo Preventivo</a>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="border-b bg-slate-50 text-left">
            <th class="px-3 py-2">Data</th>
            <th class="px-3 py-2">Titolo</th>
            <th class="px-3 py-2">Cliente</th>
            <th class="px-3 py-2">Importo</th>
            <th class="px-3 py-2">Stato</th>
            <th class="px-3 py-2">Seller</th>
            <th class="px-3 py-2">Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($quotes as $quote): ?>
            <tr class="border-b">
                <td class="px-3 py-2"><?= e(substr($quote['created_at'], 0, 10)) ?></td>
                <td class="px-3 py-2 font-medium"><?= e($quote['title']) ?></td>
                <td class="px-3 py-2"><?= e($quote['company_name']) ?></td>
                <td class="px-3 py-2">€ <?= e(number_format((float) $quote['amount'], 2, ',', '.')) ?></td>
                <td class="px-3 py-2 uppercase"><?= e($quote['status']) ?></td>
                <td class="px-3 py-2"><?= e($quote['seller_name']) ?></td>
                <td class="px-3 py-2">
                    <a class="rounded bg-slate-100 px-2 py-1 hover:bg-slate-200" href="<?= e(base_url('quotes/' . $quote['id'])) ?>">Dettaglio</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

