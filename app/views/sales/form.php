<?php
$prefillClient = $sourceQuote['client_id'] ?? 0;
?>
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="post" action="<?= e(base_url('sales')) ?>" class="grid gap-4 md:grid-cols-2">
        <?= csrf_field() ?>
        <div>
            <label class="mb-1 block text-sm font-medium">Cliente *</label>
            <select name="client_id" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Seleziona cliente</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= e((string) $client['id']) ?>" <?= selected(old('client_id', $prefillClient), $client['id']) ?>>
                        <?= e($client['company_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Preventivo collegato (opzionale)</label>
            <input type="number" min="1" name="quote_id" value="<?= e((string) old('quote_id', $prefillQuoteId ?? '')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Titolo vendita *</label>
            <input name="title" required value="<?= e((string) old('title', $sourceQuote['title'] ?? '')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Importo *</label>
            <input type="number" step="0.01" min="0.01" name="amount" required value="<?= e((string) old('amount', $sourceQuote['amount'] ?? '0.00')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Data chiusura *</label>
            <input type="date" name="closed_at" required value="<?= e((string) old('closed_at', date('Y-m-d'))) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium">Note</label>
            <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2"><?= e((string) old('notes')) ?></textarea>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-500">Registra vendita</button>
            <a href="<?= e(base_url('sales')) ?>" class="rounded border border-slate-300 px-4 py-2 hover:bg-slate-50">Annulla</a>
        </div>
    </form>
</div>

