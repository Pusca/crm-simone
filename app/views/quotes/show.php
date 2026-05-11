<div class="grid gap-4 md:grid-cols-3">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-1">
        <h2 class="text-lg font-semibold"><?= e($quote['title']) ?></h2>
        <div class="mt-3 space-y-2 text-sm">
            <p><span class="font-medium">Cliente:</span> <?= e($quote['company_name']) ?></p>
            <p><span class="font-medium">Seller:</span> <?= e($quote['seller_name']) ?></p>
            <p><span class="font-medium">Importo totale:</span> EUR <?= e(number_format((float) $quote['amount'], 2, ',', '.')) ?></p>
            <p><span class="font-medium">Stato:</span> <span class="uppercase"><?= e($quote['status']) ?></span></p>
            <p><span class="font-medium">Creato il:</span> <?= e($quote['created_at']) ?></p>
            <p><span class="font-medium">Inviato il:</span> <?= e($quote['sent_at']) ?></p>
            <p><span class="font-medium">Descrizione:</span><br><?= nl2br(e($quote['description'])) ?></p>
            <?php if (!empty($quote['pdf_path'])): ?>
                <p><a class="text-sky-700 underline" href="<?= e(base_url('quotes/' . $quote['id'] . '/pdf')) ?>">Apri PDF allegato</a></p>
            <?php endif; ?>
        </div>
        <div class="no-print mt-4 flex flex-wrap gap-2">
            <a href="<?= e(base_url('sales/create?quote_id=' . $quote['id'])) ?>" class="rounded bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-500">Converti in vendita</a>
            <a href="<?= e(base_url('quotes')) ?>" class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Torna lista</a>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-2">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-semibold">Voci preventivo</h2>
            <form method="post" action="<?= e(base_url('quotes/' . $quote['id'] . '/status')) ?>" class="no-print flex items-center gap-2">
                <?= csrf_field() ?>
                <select name="status" class="rounded border border-slate-300 px-2 py-2 text-sm">
                    <?php foreach (['draft', 'sent', 'won', 'lost'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected($quote['status'], $status) ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="rounded bg-slate-900 px-3 py-2 text-sm text-white hover:bg-slate-700" type="submit">Aggiorna stato</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b bg-slate-50 text-left">
                    <th class="px-3 py-2">Categoria</th>
                    <th class="px-3 py-2">Descrizione</th>
                    <th class="px-3 py-2">Importo</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="border-b">
                        <td class="px-3 py-2"><?= e($item['category_name']) ?></td>
                        <td class="px-3 py-2"><?= e($item['description']) ?></td>
                        <td class="px-3 py-2">EUR <?= e(number_format((float) $item['amount'], 2, ',', '.')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
