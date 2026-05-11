<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit ? base_url('appointments/' . $appointment['id'] . '/update') : base_url('appointments');
$value = static function (string $field, mixed $fallback = '') use ($appointment, $isEdit, $prefillClient, $prefillDate) {
    $oldValue = old($field, null);
    if ($oldValue !== null) {
        return $oldValue;
    }
    if ($isEdit && $appointment) {
        if (in_array($field, ['start_at', 'end_at'], true)) {
            return date('Y-m-d\TH:i', strtotime((string) $appointment[$field]));
        }
        return $appointment[$field] ?? $fallback;
    }
    if ($field === 'client_id' && $prefillClient > 0) {
        return $prefillClient;
    }
    if ($field === 'start_at') {
        return $prefillDate . 'T09:00';
    }
    if ($field === 'end_at') {
        return $prefillDate . 'T10:00';
    }
    return $fallback;
};
?>
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="post" action="<?= e($action) ?>" class="grid gap-4 md:grid-cols-2">
        <?= csrf_field() ?>
        <div>
            <label class="mb-1 block text-sm font-medium">Cliente *</label>
            <select name="client_id" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Seleziona cliente</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= e((string) $client['id']) ?>" <?= selected($value('client_id'), $client['id']) ?>>
                        <?= e($client['company_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Stage pipeline *</label>
            <select name="stage_id" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Seleziona stage</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= e((string) $stage['id']) ?>" <?= selected($value('stage_id'), $stage['id']) ?>><?= e($stage['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="mt-1 text-xs text-slate-500">Il titolo appuntamento viene generato automaticamente dallo stage selezionato.</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Inizio *</label>
            <input type="datetime-local" name="start_at" required value="<?= e((string) $value('start_at')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Fine *</label>
            <input type="datetime-local" name="end_at" required value="<?= e((string) $value('end_at')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Luogo</label>
            <input name="location" value="<?= e((string) $value('location')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium">Descrizione</label>
            <textarea name="description" rows="4" class="w-full rounded border border-slate-300 px-3 py-2"><?= e((string) $value('description')) ?></textarea>
        </div>
        <div class="no-print md:col-span-2 flex gap-2">
            <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit"><?= $isEdit ? 'Aggiorna' : 'Salva' ?> appuntamento</button>
            <a class="rounded border border-slate-300 px-4 py-2 hover:bg-slate-50" href="<?= e(base_url('appointments')) ?>">Annulla</a>
        </div>
    </form>
</div>
