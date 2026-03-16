<div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="mb-2 text-2xl font-semibold">Accesso CRM</h1>
    <p class="mb-6 text-sm text-slate-600">Inserisci le credenziali per accedere.</p>

    <form method="post" action="<?= e(base_url('login')) ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e(old('email')) ?>" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="password">Password</label>
            <input id="password" name="password" type="password" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-700">Login</button>
    </form>
</div>

