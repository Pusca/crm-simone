<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit ? base_url('activities/' . $activity['id'] . '/update') : base_url('activities');
$value = static function (string $field, mixed $fallback = '') use ($activity, $isEdit, $prefillClient) {
    $oldValue = old($field, null);
    if ($oldValue !== null) {
        return $oldValue;
    }
    if ($isEdit && $activity) {
        if (in_array($field, ['occurred_at', 'next_action_at'], true) && !empty($activity[$field])) {
            return date('Y-m-d\TH:i', strtotime((string) $activity[$field]));
        }
        return $activity[$field] ?? $fallback;
    }
    if ($field === 'client_id' && $prefillClient > 0) {
        return $prefillClient;
    }
    if ($field === 'occurred_at') {
        return date('Y-m-d\TH:i');
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
                <?php foreach ($clients as $clientRow): ?>
                    <option value="<?= e((string) $clientRow['id']) ?>" <?= selected($value('client_id'), $clientRow['id']) ?>>
                        <?= e($clientRow['company_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Tipo attività *</label>
            <select name="type" required class="w-full rounded border border-slate-300 px-3 py-2">
                <?php foreach (['call', 'email', 'whatsapp', 'meeting', 'other'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= selected($value('type', 'call'), $type) ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Stage pipeline (opzionale)</label>
            <select name="stage_id" class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Nessuno</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= e((string) $stage['id']) ?>" <?= selected($value('stage_id'), $stage['id']) ?>><?= e($stage['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Data/Ora attività *</label>
            <input type="datetime-local" name="occurred_at" required value="<?= e((string) $value('occurred_at')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium">Oggetto *</label>
            <input name="subject" required value="<?= e((string) $value('subject')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium">Dettaglio</label>
            <textarea name="body" rows="4" class="w-full rounded border border-slate-300 px-3 py-2"><?= e((string) $value('body')) ?></textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Prossima azione (opzionale)</label>
            <input type="datetime-local" name="next_action_at" value="<?= e((string) $value('next_action_at')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>

        <div class="md:col-span-2 flex gap-2">
            <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit"><?= $isEdit ? 'Aggiorna' : 'Salva' ?> attività</button>
            <a class="rounded border border-slate-300 px-4 py-2 hover:bg-slate-50" href="<?= e(base_url('activities')) ?>">Annulla</a>
        </div>
    </form>
</div>
