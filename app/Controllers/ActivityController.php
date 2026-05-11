<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Activity;
use App\Models\Client;
use App\Models\PipelineStage;

final class ActivityController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $filters = [
            'type' => (string) $this->get('type', ''),
            'stage_id' => (string) $this->get('stage_id', ''),
            'client_id' => (string) $this->get('client_id', ''),
            'date_from' => (string) $this->get('date_from', ''),
            'date_to' => (string) $this->get('date_to', ''),
        ];

        $activities = Activity::search($filters, $user);
        $clients = Client::forSelect($user);
        $stages = PipelineStage::active();

        $this->view('activities/index', [
            'title' => 'Attività / Contatti / Mail / WhatsApp',
            'activities' => $activities,
            'clients' => $clients,
            'stages' => $stages,
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $clients = Client::forSelect($user);
        $stages = PipelineStage::active();
        $activity = null;
        $prefillClient = (int) $this->get('client_id', 0);

        $this->view('activities/form', [
            'title' => 'Nuova Attività',
            'mode' => 'create',
            'activity' => $activity,
            'clients' => $clients,
            'stages' => $stages,
            'prefillClient' => $prefillClient,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();

        $data = $this->collectData($user);
        $errors = $this->validateData($data, $user);
        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('activities/create');
        }

        Activity::create($data);
        set_flash('success', 'Attività registrata.');
        $this->redirect('activities');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $activity = Activity::findAccessible((int) $id, $user);
        if (!$activity) {
            http_response_code(404);
            echo 'Attività non trovata.';
            return;
        }

        $clients = Client::forSelect($user);
        $stages = PipelineStage::active();
        $this->view('activities/form', [
            'title' => 'Modifica Attività',
            'mode' => 'edit',
            'activity' => $activity,
            'clients' => $clients,
            'stages' => $stages,
            'prefillClient' => 0,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $activity = Activity::findAccessible((int) $id, $user);
        if (!$activity) {
            http_response_code(404);
            echo 'Attività non trovata.';
            return;
        }

        $data = $this->collectData($user);
        $errors = $this->validateData($data, $user);
        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('activities/' . (int) $id . '/edit');
        }

        Activity::update((int) $id, $data);
        set_flash('success', 'Attività aggiornata.');
        $this->redirect('activities');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $activity = Activity::findAccessible((int) $id, $user);
        if (!$activity) {
            http_response_code(404);
            echo 'Attività non trovata.';
            return;
        }

        Activity::delete((int) $id);
        set_flash('success', 'Attività eliminata.');
        $this->redirect('activities');
    }

    private function collectData(array $user): array
    {
        return [
            'client_id' => (int) $this->post('client_id', 0),
            'user_id' => (int) $user['id'],
            'type' => (string) $this->post('type', 'call'),
            'stage_id' => (string) $this->post('stage_id', ''),
            'subject' => trim((string) $this->post('subject', '')),
            'body' => trim((string) $this->post('body', '')),
            'occurred_at' => $this->normalizeDateTimeInput((string) $this->post('occurred_at', '')),
            'next_action_at' => $this->normalizeDateTimeInput((string) $this->post('next_action_at', '')),
        ];
    }

    private function validateData(array $data, array $user): array
    {
        $errors = [];
        if (!in_array($data['type'], ['call', 'email', 'whatsapp', 'meeting', 'other'], true)) {
            $errors[] = 'Tipo attività non valido.';
        }
        if ($data['stage_id'] !== '' && !PipelineStage::find((int) $data['stage_id'])) {
            $errors[] = 'Stage pipeline non valido.';
        }
        if ($data['client_id'] <= 0) {
            $errors[] = 'Cliente obbligatorio.';
        } else {
            $client = Client::findAccessible($data['client_id'], $user);
            if (!$client) {
                $errors[] = 'Cliente non accessibile.';
            }
        }
        if ($data['subject'] === '') {
            $errors[] = 'Oggetto obbligatorio.';
        }
        if ($data['occurred_at'] === '') {
            $errors[] = 'Data/ora attività obbligatoria.';
        } elseif ($this->parseDateTimeInput($data['occurred_at']) === null) {
            $errors[] = 'Data/ora attività non valida.';
        }
        if ($data['next_action_at'] !== '' && $this->parseDateTimeInput($data['next_action_at']) === null) {
            $errors[] = 'Prossima azione non valida.';
        }
        return $errors;
    }
}
