<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold">Nuova macro-categoria</h2>
    <form method="post" action="<?= e(base_url('config/categories')) ?>" class="grid gap-3 md:grid-cols-4">
        <?= csrf_field() ?>
        <input name="name" required placeholder="Nome categoria" class="rounded border border-slate-300 px-3 py-2">
        <input type="number" name="sort_order" value="10" class="rounded border border-slate-300 px-3 py-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Attiva</label>
        <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700" type="submit">Aggiungi</button>
    </form>
</section>

<section class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="mb-3 text-lg font-semibold">Categorie esistenti</h2>
    <table class="min-w-full text-sm">
        <thead>
        <tr class="border-b bg-slate-50 text-left">
            <th class="px-3 py-2">ID</th>
            <th class="px-3 py-2">Nome</th>
            <th class="px-3 py-2">Ordine</th>
            <th class="px-3 py-2">Attiva</th>
            <th class="px-3 py-2">Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr class="border-b">
                <td class="px-3 py-2"><?= e((string) $category['id']) ?></td>
                <td class="px-3 py-2">
                    <form method="post" action="<?= e(base_url('config/categories/' . $category['id'] . '/update')) ?>" class="flex flex-wrap items-center gap-2">
                        <?= csrf_field() ?>
                        <input name="name" value="<?= e($category['name']) ?>" class="rounded border border-slate-300 px-2 py-1">
                        <input type="number" name="sort_order" value="<?= e((string) $category['sort_order']) ?>" class="w-20 rounded border border-slate-300 px-2 py-1">
                        <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_active" value="1" <?= (int) $category['is_active'] === 1 ? 'checked' : '' ?>> attiva</label>
                        <button class="rounded bg-sky-100 px-2 py-1 text-sky-700 hover:bg-sky-200" type="submit">Salva</button>
                    </form>
                </td>
                <td class="px-3 py-2"><?= e((string) $category['sort_order']) ?></td>
                <td class="px-3 py-2"><?= (int) $category['is_active'] === 1 ? 'Si' : 'No' ?></td>
                <td class="px-3 py-2">
                    <form method="post" action="<?= e(base_url('config/categories/' . $category['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminare categoria?');">
                        <?= csrf_field() ?>
                        <button class="rounded bg-red-100 px-2 py-1 text-red-700 hover:bg-red-200" type="submit">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

