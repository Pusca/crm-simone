<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PipelineStage;
use App\Models\QuoteCategory;
use App\Models\Target;
use App\Models\User;
use Throwable;

final class ConfigController extends Controller
{
    public function pipeline(): void
    {
        $this->requireManager();
        $stages = PipelineStage::all();
        $this->view('config/pipeline', [
            'title' => 'Configurazione Pipeline',
            'stages' => $stages,
        ]);
    }

    public function storePipeline(): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();

        $name = trim((string) $this->post('name', ''));
        $sortOrder = (int) $this->post('sort_order', 0);
        $isActive = $this->post('is_active', 0);
        if ($name === '') {
            set_flash('error', 'Nome stage obbligatorio.');
            $this->redirect('config/pipeline');
        }
        PipelineStage::create([
            'name' => $name,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);
        set_flash('success', 'Stage pipeline creato.');
        $this->redirect('config/pipeline');
    }

    public function updatePipeline(string $id): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();
        $name = trim((string) $this->post('name', ''));
        if ($name === '') {
            set_flash('error', 'Nome stage obbligatorio.');
            $this->redirect('config/pipeline');
        }
        PipelineStage::update((int) $id, [
            'name' => $name,
            'sort_order' => (int) $this->post('sort_order', 0),
            'is_active' => $this->post('is_active', 0),
        ]);
        set_flash('success', 'Stage aggiornato.');
        $this->redirect('config/pipeline');
    }

    public function deletePipeline(string $id): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();
        try {
            PipelineStage::delete((int) $id);
            set_flash('success', 'Stage eliminato.');
        } catch (Throwable) {
            set_flash('error', 'Impossibile eliminare stage: è già usato in attività/appuntamenti.');
        }
        $this->redirect('config/pipeline');
    }

    public function categories(): void
    {
        $this->requireManager();
        $categories = QuoteCategory::all();
        $this->view('config/categories', [
            'title' => 'Macro-Categorie Preventivi',
            'categories' => $categories,
        ]);
    }

    public function storeCategory(): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();
        $name = trim((string) $this->post('name', ''));
        if ($name === '') {
            set_flash('error', 'Nome categoria obbligatorio.');
            $this->redirect('config/categories');
        }
        QuoteCategory::create([
            'name' => $name,
            'sort_order' => (int) $this->post('sort_order', 0),
            'is_active' => $this->post('is_active', 0),
        ]);
        set_flash('success', 'Categoria creata.');
        $this->redirect('config/categories');
    }

    public function updateCategory(string $id): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();
        $name = trim((string) $this->post('name', ''));
        if ($name === '') {
            set_flash('error', 'Nome categoria obbligatorio.');
            $this->redirect('config/categories');
        }
        QuoteCategory::update((int) $id, [
            'name' => $name,
            'sort_order' => (int) $this->post('sort_order', 0),
            'is_active' => $this->post('is_active', 0),
        ]);
        set_flash('success', 'Categoria aggiornata.');
        $this->redirect('config/categories');
    }

    public function deleteCategory(string $id): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();
        try {
            QuoteCategory::delete((int) $id);
            set_flash('success', 'Categoria eliminata.');
        } catch (Throwable) {
            set_flash('error', 'Impossibile eliminare categoria: è già usata in uno o più preventivi.');
        }
        $this->redirect('config/categories');
    }

    public function targets(): void
    {
        $this->requireManager();
        $filters = [
            'user_id' => (string) $this->get('user_id', ''),
            'period' => (string) $this->get('period', ''),
        ];
        $targets = Target::list($filters);
        $users = array_filter(User::all(), static fn(array $u): bool => $u['role'] === 'seller');

        $this->view('config/targets', [
            'title' => 'Target Venditori',
            'targets' => $targets,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function storeTargets(): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();

        $userId = (int) $this->post('user_id', 0);
        $period = (string) $this->post('period', 'weekly');
        $startDate = (string) $this->post('start_date', '');
        $endDate = (string) $this->post('end_date', '');
        $appointments = (int) $this->post('appointments_target', 0);
        $quotes = (int) $this->post('quotes_target', 0);
        $sales = (int) $this->post('sales_target', 0);

        $errors = [];
        if ($userId <= 0) {
            $errors[] = 'Venditore obbligatorio.';
        }
        if (!in_array($period, ['weekly', 'monthly'], true)) {
            $errors[] = 'Periodo target non valido.';
        }
        if ($startDate === '' || $endDate === '') {
            $errors[] = 'Date inizio/fine obbligatorie.';
        } elseif (strtotime($startDate) > strtotime($endDate)) {
            $errors[] = 'La data fine deve essere uguale o successiva alla data inizio.';
        }

        if ($errors !== []) {
            set_flash('error', implode(' ', $errors));
            $this->redirect('config/targets');
        }

        Target::createMany([
            [
                'user_id' => $userId,
                'period' => $period,
                'metric' => 'appointments',
                'target_value' => $appointments,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            [
                'user_id' => $userId,
                'period' => $period,
                'metric' => 'quotes',
                'target_value' => $quotes,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            [
                'user_id' => $userId,
                'period' => $period,
                'metric' => 'sales',
                'target_value' => $sales,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);

        set_flash('success', 'Target salvati.');
        $this->redirect('config/targets');
    }
}
