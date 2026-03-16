<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="<?= e(base_url('sales')) ?>" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Ricerca</label>
            <input name="q" value="<?= e($filters['q'] ?? '') ?>" class="rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Titolo o cliente">
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
        <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700">Filtra</button>
    </form>
    <a href="<?= e(base_url('sales/create')) ?>" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">+ Nuova Vendita</a>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="border-b bg-slate-50 text-left">
            <th class="px-3 py-2">Data chiusura</th>
            <th class="px-3 py-2">Titolo</th>
            <th class="px-3 py-2">Cliente</th>
            <th class="px-3 py-2">Importo</th>
            <th class="px-3 py-2">Preventivo</th>
            <th class="px-3 py-2">Seller</th>
            <th class="px-3 py-2">Note</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sales as $sale): ?>
            <tr class="border-b">
                <td class="px-3 py-2"><?= e($sale['closed_at']) ?></td>
                <td class="px-3 py-2 font-medium"><?= e($sale['title']) ?></td>
                <td class="px-3 py-2"><?= e($sale['company_name']) ?></td>
                <td class="px-3 py-2">€ <?= e(number_format((float) $sale['amount'], 2, ',', '.')) ?></td>
                <td class="px-3 py-2"><?= e($sale['quote_title']) ?></td>
                <td class="px-3 py-2"><?= e($sale['seller_name']) ?></td>
                <td class="px-3 py-2"><?= e($sale['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

