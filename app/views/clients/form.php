<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit ? base_url('clients/' . $client['id'] . '/update') : base_url('clients');
$value = static function (string $field, mixed $fallback = '') use ($client, $isEdit) {
    $oldValue = old($field, null);
    if ($oldValue !== null) {
        return $oldValue;
    }
    if ($isEdit && $client) {
        return $client[$field] ?? $fallback;
    }
    return $fallback;
};
?>

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="post" action="<?= e($action) ?>" class="grid gap-4 md:grid-cols-2">
        <?= csrf_field() ?>
        <div>
            <label class="mb-1 block text-sm font-medium">Nome Azienda *</label>
            <input name="company_name" required value="<?= e((string) $value('company_name')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Referente *</label>
            <input name="contact_name" required value="<?= e((string) $value('contact_name')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <input type="email" name="email" value="<?= e((string) $value('email')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Telefono</label>
            <input name="phone" value="<?= e((string) $value('phone')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Indirizzo</label>
            <input name="address" value="<?= e((string) $value('address')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Sito Web (https://...)</label>
            <input name="website" value="<?= e((string) $value('website')) ?>" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium">Note</label>
            <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2"><?= e((string) $value('notes')) ?></textarea>
        </div>

        <?php if (($currentUser['role'] ?? 'seller') === 'manager'): ?>
            <div>
                <label class="mb-1 block text-sm font-medium">Owner</label>
                <select name="owner_user_id" class="w-full rounded border border-slate-300 px-3 py-2">
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?= e((string) $owner['id']) ?>" <?= selected($value('owner_user_id', $currentUser['id']), $owner['id']) ?>>
                            <?= e($owner['name']) ?> (<?= e($owner['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input id="is_shared" type="checkbox" name="is_shared" value="1" <?= (int) $value('is_shared', 0) === 1 ? 'checked' : '' ?>>
                <label for="is_shared" class="text-sm">Cliente condiviso (visibile a tutti i seller)</label>
            </div>
        <?php else: ?>
            <input type="hidden" name="owner_user_id" value="<?= e((string) $currentUser['id']) ?>">
        <?php endif; ?>

        <div class="md:col-span-2 flex gap-2">
            <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit"><?= $isEdit ? 'Aggiorna' : 'Crea' ?> cliente</button>
            <a class="rounded border border-slate-300 px-4 py-2 hover:bg-slate-50" href="<?= e(base_url('clients')) ?>">Annulla</a>
        </div>
    </form>
</div>

