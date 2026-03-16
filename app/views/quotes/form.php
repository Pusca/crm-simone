<?php
$oldInput = $GLOBALS['_old_input'] ?? [];
$itemCategories = $oldInput['item_category_id'] ?? [($categories[0]['id'] ?? '')];
$itemDescriptions = $oldInput['item_description'] ?? [''];
$itemAmounts = $oldInput['item_amount'] ?? ['0.00'];
$rowCount = max(count($itemCategories), count($itemDescriptions), count($itemAmounts), 1);
?>

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="post" action="<?= e(base_url('quotes')) ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Cliente *</label>
                <select name="client_id" required class="w-full rounded border border-slate-300 px-3 py-2">
                    <option value="">Seleziona cliente</option>
                    <?php foreach ($clients as $client): ?>
                        <?php $selectedClient = old('client_id', $prefillClient ?? 0); ?>
                        <option value="<?= e((string) $client['id']) ?>" <?= selected($selectedClient, $client['id']) ?>><?= e($client['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Attività origine (opzionale)</label>
                <input type="number" min="1" name="source_activity_id" value="<?= e((string) old('source_activity_id', $prefillActivity ?? '')) ?>" class="w-full rounded border border-slate-300 px-3 py-2" placeholder="ID attività">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Titolo *</label>
                <input name="title" required value="<?= e((string) old('title')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Stato *</label>
                <select name="status" class="w-full rounded border border-slate-300 px-3 py-2">
                    <?php foreach (['draft', 'sent', 'won', 'lost'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected(old('status', 'draft'), $status) ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Data invio (se sent)</label>
                <input type="date" name="sent_at" value="<?= e((string) old('sent_at')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">PDF (opzionale)</label>
                <input type="file" name="pdf_file" accept="application/pdf,.pdf" class="w-full rounded border border-slate-300 px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">Max 5MB, solo PDF.</p>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium">Descrizione</label>
                <textarea name="description" rows="3" class="w-full rounded border border-slate-300 px-3 py-2"><?= e((string) old('description')) ?></textarea>
            </div>
        </div>

        <section class="rounded border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Righe preventivo (macro-categorie)</h2>
                <button id="addItemRow" type="button" class="rounded bg-slate-900 px-3 py-2 text-sm text-white hover:bg-slate-700">+ Aggiungi riga</button>
            </div>
            <div id="itemsContainer" class="space-y-2">
                <?php for ($i = 0; $i < $rowCount; $i++): ?>
                    <div class="item-row grid gap-2 rounded border border-slate-200 p-3 md:grid-cols-12">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs uppercase text-slate-500">Categoria *</label>
                            <select name="item_category_id[]" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e((string) $category['id']) ?>" <?= selected($itemCategories[$i] ?? '', $category['id']) ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-6">
                            <label class="mb-1 block text-xs uppercase text-slate-500">Descrizione</label>
                            <input name="item_description[]" value="<?= e((string) ($itemDescriptions[$i] ?? '')) ?>" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs uppercase text-slate-500">Importo *</label>
                            <input step="0.01" type="number" min="0" name="item_amount[]" value="<?= e((string) ($itemAmounts[$i] ?? '0.00')) ?>" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" class="remove-item w-full rounded bg-red-100 px-2 py-2 text-sm text-red-700 hover:bg-red-200">X</button>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <div class="flex gap-2">
            <button class="rounded bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-500" type="submit">Salva preventivo</button>
            <a class="rounded border border-slate-300 px-4 py-2 hover:bg-slate-50" href="<?= e(base_url('quotes')) ?>">Annulla</a>
        </div>
    </form>
</div>

<template id="itemTemplate">
    <div class="item-row grid gap-2 rounded border border-slate-200 p-3 md:grid-cols-12">
        <div class="md:col-span-3">
            <label class="mb-1 block text-xs uppercase text-slate-500">Categoria *</label>
            <select name="item_category_id[]" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-6">
            <label class="mb-1 block text-xs uppercase text-slate-500">Descrizione</label>
            <input name="item_description[]" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-xs uppercase text-slate-500">Importo *</label>
            <input step="0.01" type="number" min="0" name="item_amount[]" value="0.00" class="w-full rounded border border-slate-300 px-2 py-2 text-sm">
        </div>
        <div class="md:col-span-1 flex items-end">
            <button type="button" class="remove-item w-full rounded bg-red-100 px-2 py-2 text-sm text-red-700 hover:bg-red-200">X</button>
        </div>
    </div>
</template>

<script>
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemTemplate');
    document.getElementById('addItemRow').addEventListener('click', () => {
        container.insertAdjacentHTML('beforeend', template.innerHTML);
    });
    container.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-item')) return;
        const rows = container.querySelectorAll('.item-row');
        if (rows.length <= 1) return;
        event.target.closest('.item-row').remove();
    });
</script>

