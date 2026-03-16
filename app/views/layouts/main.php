<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>">
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<?php if ($user): ?>
    <div class="min-h-screen lg:flex">
        <div class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden" data-sidebar-overlay></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col border-r border-slate-700 bg-slate-900 text-slate-100 transition-transform duration-200 ease-out lg:static lg:w-72 lg:max-w-none lg:translate-x-0 lg:border-b-0" data-sidebar>
            <div class="flex items-center justify-between px-6 py-4">
                <a href="<?= e(base_url('dashboard')) ?>" class="text-lg font-semibold tracking-wide">Mini CRM</a>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-slate-700 px-2 py-1 text-xs uppercase"><?= e($user['role']) ?></span>
                    <button type="button" class="rounded bg-slate-800 px-2 py-1 text-xs text-slate-200 hover:bg-slate-700 lg:hidden" data-sidebar-close>Chiudi</button>
                </div>
            </div>

            <nav class="px-4 pb-6">
                <a class="sidebar-link <?= is_active_path('/dashboard') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('dashboard')) ?>" data-sidebar-link>Dashboard</a>
                <a class="sidebar-link <?= is_active_path('/clients') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('clients')) ?>" data-sidebar-link>Aziende / Clienti</a>
                <a class="sidebar-link <?= is_active_path('/activities') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('activities')) ?>" data-sidebar-link>Attivita / Contatti</a>
                <a class="sidebar-link <?= is_active_path('/appointments') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('appointments')) ?>" data-sidebar-link>Agenda</a>
                <a class="sidebar-link <?= is_active_path('/quotes') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('quotes')) ?>" data-sidebar-link>Preventivi</a>
                <a class="sidebar-link <?= is_active_path('/sales') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('sales')) ?>" data-sidebar-link>Vendite</a>

                <?php if ($user['role'] === 'manager'): ?>
                    <div class="mt-5 border-t border-slate-700 pt-4">
                        <p class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-400">Config</p>
                        <a class="sidebar-link <?= is_active_path('/config/pipeline') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('config/pipeline')) ?>" data-sidebar-link>Pipeline</a>
                        <a class="sidebar-link <?= is_active_path('/config/categories') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('config/categories')) ?>" data-sidebar-link>Categorie Preventivi</a>
                        <a class="sidebar-link <?= is_active_path('/config/targets') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('config/targets')) ?>" data-sidebar-link>Target</a>
                        <a class="sidebar-link <?= is_active_path('/users') ? 'sidebar-link-active' : '' ?>" href="<?= e(base_url('users')) ?>" data-sidebar-link>Utenti</a>
                    </div>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="flex-1 p-4 lg:p-8">
            <div class="mb-4 flex items-center justify-between gap-3 lg:hidden">
                <button type="button" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" data-sidebar-open aria-expanded="false">Menu</button>
                <span class="text-xs font-medium uppercase tracking-wide text-slate-500"><?= e($user['role']) ?></span>
            </div>

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold"><?= e($title ?? '') ?></h1>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm text-slate-600"><?= e($user['name']) ?> (<?= e($user['email']) ?>)</span>
                    <form method="post" action="<?= e(base_url('logout')) ?>">
                        <?= csrf_field() ?>
                        <button class="rounded bg-slate-800 px-3 py-2 text-sm text-white hover:bg-slate-700" type="submit">Logout</button>
                    </form>
                </div>
            </div>

            <?php if ($error = flash('error')): ?>
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success = flash('success')): ?>
                <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700"><?= e($success) ?></div>
            <?php endif; ?>

            <?php require $contentView; ?>
        </main>
    </div>
<?php else: ?>
    <main class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">
            <?php if ($error = flash('error')): ?>
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success = flash('success')): ?>
                <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700"><?= e($success) ?></div>
            <?php endif; ?>
            <?php require $contentView; ?>
        </div>
    </main>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const openButtons = document.querySelectorAll('[data-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');
    const navLinks = document.querySelectorAll('[data-sidebar-link]');

    if (!sidebar || !overlay) {
        return;
    }

    const isDesktop = () => window.innerWidth >= 1024;

    const setSidebarOpen = (open) => {
        if (isDesktop()) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        }

        openButtons.forEach((button) => {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => setSidebarOpen(true));
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setSidebarOpen(false));
    });

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (!isDesktop()) {
                setSidebarOpen(false);
            }
        });
    });

    overlay.addEventListener('click', () => setSidebarOpen(false));
    window.addEventListener('resize', () => setSidebarOpen(false));
    setSidebarOpen(false);
});
</script>
</body>
</html>