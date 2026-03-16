<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="<?= e(base_url('clients')) ?>" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium uppercase text-slate-500">Ricerca</label>
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Azienda, referente, email..." class="rounded border border-slate-300 px-3 py-2 text-sm">
        </div>
        <?php if (($currentUser['role'] ?? 'seller') === 'manager'): ?>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500">Owner</label>
                <select name="owner_user_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Tutti</option>
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?= e((string) $owner['id']) ?>" <?= selected($filters['owner_user_id'] ?? '', $owner['id']) ?>>
                            <?= e($owner['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700" type="submit">Filtra</button>
    </form>
    <a href="<?= e(base_url('clients/create')) ?>" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">+ Nuovo Cliente</a>
</div>

<div class="grid gap-3 md:hidden">
    <?php foreach ($clients as $client): ?>
        <?php $canEditClient = ($currentUser['role'] === 'manager') || ((int) $client['owner_user_id'] === (int) $currentUser['id']); ?>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <a class="text-base font-semibold text-slate-900 underline-offset-2 hover:underline" href="<?= e(base_url('clients/' . $client['id'])) ?>">
                        <?= e($client['company_name']) ?>
                    </a>
                    <p class="mt-1 text-sm text-slate-600"><?= e($client['contact_name']) ?></p>
                </div>
                <span class="rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                    <?= ((int) $client['is_shared'] === 1) ? 'Condiviso' : 'Privato' ?>
                </span>
            </div>

            <div class="mt-3 space-y-1 text-sm text-slate-600">
                <p><span class="font-medium text-slate-800">Email:</span> <?= e($client['email'] ?: '-') ?></p>
                <p><span class="font-medium text-slate-800">Telefono:</span> <?= e($client['phone'] ?: '-') ?></p>
                <p><span class="font-medium text-slate-800">Owner:</span> <?= e($client['owner_name']) ?></p>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a class="rounded bg-slate-900 px-3 py-2 text-sm text-white hover:bg-slate-700" href="<?= e(base_url('clients/' . $client['id'])) ?>">Apri dettaglio</a>
                <?php if ($canEditClient): ?>
                    <a class="rounded bg-sky-100 px-3 py-2 text-sm text-sky-700 hover:bg-sky-200" href="<?= e(base_url('clients/' . $client['id'] . '/edit')) ?>">Modifica</a>
                <?php endif; ?>
                <a class="rounded bg-violet-100 px-3 py-2 text-sm text-violet-700 hover:bg-violet-200" href="<?= e(base_url('activities/create?client_id=' . $client['id'])) ?>">Attivita</a>
                <a class="rounded bg-emerald-100 px-3 py-2 text-sm text-emerald-700 hover:bg-emerald-200" href="<?= e(base_url('appointments/create?client_id=' . $client['id'])) ?>">Appuntamento</a>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if (count($clients) === 0): ?>
        <p class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500 shadow-sm">Nessun cliente trovato con i filtri correnti.</p>
    <?php endif; ?>
</div>

<div class="hidden overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm md:block">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="border-b bg-slate-50 text-left">
            <th class="px-3 py-2">Azienda</th>
            <th class="px-3 py-2">Referente</th>
            <th class="px-3 py-2">Contatti</th>
            <th class="px-3 py-2">Owner</th>
            <th class="px-3 py-2">Condiviso</th>
            <th class="px-3 py-2">Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($clients as $client): ?>
            <?php $canEditClient = ($currentUser['role'] === 'manager') || ((int) $client['owner_user_id'] === (int) $currentUser['id']); ?>
            <tr class="border-b">
                <td class="px-3 py-2 font-medium">
                    <a class="underline-offset-2 hover:underline" href="<?= e(base_url('clients/' . $client['id'])) ?>">
                        <?= e($client['company_name']) ?>
                    </a>
                </td>
                <td class="px-3 py-2"><?= e($client['contact_name']) ?></td>
                <td class="px-3 py-2">
                    <div><?= e($client['email']) ?></div>
                    <div class="text-slate-500"><?= e($client['phone']) ?></div>
                </td>
                <td class="px-3 py-2"><?= e($client['owner_name']) ?></td>
                <td class="px-3 py-2"><?= ((int) $client['is_shared'] === 1) ? 'Si' : 'No' ?></td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap gap-1">
                        <a class="rounded bg-slate-100 px-2 py-1 hover:bg-slate-200" href="<?= e(base_url('clients/' . $client['id'])) ?>">Dettaglio</a>
                        <?php if ($canEditClient): ?>
                            <a class="rounded bg-sky-100 px-2 py-1 text-sky-700 hover:bg-sky-200" href="<?= e(base_url('clients/' . $client['id'] . '/edit')) ?>">Modifica</a>
                        <?php endif; ?>
                        <a class="rounded bg-violet-100 px-2 py-1 text-violet-700 hover:bg-violet-200" href="<?= e(base_url('activities/create?client_id=' . $client['id'])) ?>">Nuova Attivita</a>
                        <a class="rounded bg-emerald-100 px-2 py-1 text-emerald-700 hover:bg-emerald-200" href="<?= e(base_url('appointments/create?client_id=' . $client['id'])) ?>">Nuovo App.</a>
                        <a class="rounded bg-amber-100 px-2 py-1 text-amber-700 hover:bg-amber-200" href="<?= e(base_url('quotes/create?client_id=' . $client['id'])) ?>">Nuovo Preventivo</a>
                        <?php if ($canEditClient): ?>
                            <form method="post" action="<?= e(base_url('clients/' . $client['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminare il cliente?');">
                                <?= csrf_field() ?>
                                <button class="rounded bg-red-100 px-2 py-1 text-red-700 hover:bg-red-200" type="submit">Elimina</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($clients) === 0): ?>
            <tr>
                <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500">Nessun cliente trovato con i filtri correnti.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>