<?php $timelineCount = count($timeline); ?>

<div class="grid gap-4 md:grid-cols-3">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-1">
        <div class="mb-4">
            <a class="text-sm font-medium text-slate-600 underline-offset-2 hover:text-slate-900 hover:underline" href="<?= e(base_url('clients')) ?>">Torna ai clienti</a>
        </div>

        <h2 class="text-lg font-semibold"><?= e($client['company_name']) ?></h2>
        <div class="mt-3 space-y-2 text-sm">
            <p><span class="font-medium">Referente:</span> <?= e($client['contact_name']) ?></p>
            <p>
                <span class="font-medium">Email:</span>
                <?php if (!empty($client['email'])): ?>
                    <a class="text-sky-700 underline-offset-2 hover:underline" href="mailto:<?= e($client['email']) ?>"><?= e($client['email']) ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p>
                <span class="font-medium">Telefono:</span>
                <?php if (!empty($client['phone'])): ?>
                    <a class="text-sky-700 underline-offset-2 hover:underline" href="tel:<?= e($client['phone']) ?>"><?= e($client['phone']) ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p><span class="font-medium">Indirizzo:</span> <?= e($client['address'] ?: '-') ?></p>
            <p>
                <span class="font-medium">Sito:</span>
                <?php if (!empty($client['website'])): ?>
                    <a class="text-sky-700 underline-offset-2 hover:underline" href="<?= e($client['website']) ?>" target="_blank" rel="noreferrer"><?= e($client['website']) ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
            <p><span class="font-medium">Owner:</span> <?= e($client['owner_name']) ?></p>
            <p><span class="font-medium">Condiviso:</span> <?= ((int) $client['is_shared'] === 1) ? 'Si' : 'No' ?></p>
            <p><span class="font-medium">Note:</span><br><?= nl2br(e($client['notes'] ?: '-')) ?></p>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <?php $viewer = \App\Core\Auth::user(); ?>
            <?php $canEditClient = $viewer && (($viewer['role'] === 'manager') || ((int) $viewer['id'] === (int) $client['owner_user_id'])); ?>
            <?php if ($canEditClient): ?>
                <a class="rounded bg-slate-900 px-3 py-2 text-sm text-white hover:bg-slate-700" href="<?= e(base_url('clients/' . $client['id'] . '/edit')) ?>">Modifica cliente</a>
            <?php endif; ?>
            <a class="rounded bg-violet-600 px-3 py-2 text-sm text-white hover:bg-violet-500" href="<?= e(base_url('activities/create?client_id=' . $client['id'])) ?>">Nuova attivita</a>
            <a class="rounded bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-500" href="<?= e(base_url('appointments/create?client_id=' . $client['id'])) ?>">Nuovo appuntamento</a>
            <a class="rounded bg-amber-500 px-3 py-2 text-sm text-white hover:bg-amber-400" href="<?= e(base_url('quotes/create?client_id=' . $client['id'])) ?>">Nuovo preventivo</a>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Timeline cliente</h2>
            <span class="rounded bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-wide text-slate-600"><?= e((string) $timelineCount) ?> eventi</span>
        </div>

        <div class="space-y-3">
            <?php if ($timelineCount === 0): ?>
                <section class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Nessun evento registrato</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Lo storico di questo cliente si popola automaticamente quando registri attivita, appuntamenti,
                        preventivi e vendite collegate a questa anagrafica.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="rounded bg-violet-600 px-3 py-2 text-sm text-white hover:bg-violet-500" href="<?= e(base_url('activities/create?client_id=' . $client['id'])) ?>">Registra attivita</a>
                        <a class="rounded bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-500" href="<?= e(base_url('appointments/create?client_id=' . $client['id'])) ?>">Crea appuntamento</a>
                        <a class="rounded bg-amber-500 px-3 py-2 text-sm text-white hover:bg-amber-400" href="<?= e(base_url('quotes/create?client_id=' . $client['id'])) ?>">Crea preventivo</a>
                    </div>
                </section>
            <?php else: ?>
                <?php foreach ($timeline as $event): ?>
                    <article class="rounded-lg border border-slate-200 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500"><?= e($event['event_type']) ?> - <?= e($event['event_at']) ?></p>
                        <h3 class="font-medium"><?= e($event['title']) ?></h3>
                        <p class="text-sm text-slate-600"><?= nl2br(e($event['description'] ?? '')) ?></p>
                        <p class="mt-1 text-xs text-slate-500">Operatore: <?= e($event['actor']) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>