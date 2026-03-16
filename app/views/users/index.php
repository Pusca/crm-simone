<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold">Crea utente</h2>
    <form method="post" action="<?= e(base_url('users')) ?>" class="grid gap-3 md:grid-cols-4">
        <?= csrf_field() ?>
        <input name="name" required placeholder="Nome completo" class="rounded border border-slate-300 px-3 py-2">
        <input type="email" name="email" required placeholder="Email" class="rounded border border-slate-300 px-3 py-2">
        <input type="password" name="password" required placeholder="Password" class="rounded border border-slate-300 px-3 py-2">
        <select name="role" class="rounded border border-slate-300 px-3 py-2">
            <option value="seller">Seller</option>
            <option value="manager">Manager</option>
        </select>
        <button class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700 md:col-span-4" type="submit">Crea utente</button>
    </form>
</section>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="mb-3 text-lg font-semibold">Utenti registrati</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="border-b bg-slate-50 text-left">
                <th class="px-3 py-2">ID</th>
                <th class="px-3 py-2">Nome</th>
                <th class="px-3 py-2">Email</th>
                <th class="px-3 py-2">Ruolo</th>
                <th class="px-3 py-2">Creato il</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-b">
                    <td class="px-3 py-2"><?= e((string) $u['id']) ?></td>
                    <td class="px-3 py-2"><?= e($u['name']) ?></td>
                    <td class="px-3 py-2"><?= e($u['email']) ?></td>
                    <td class="px-3 py-2"><?= e($u['role']) ?></td>
                    <td class="px-3 py-2"><?= e($u['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

