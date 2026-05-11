<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PipelineStage;

final class AppointmentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $reference = (string) $this->get('week', date('Y-m-d'));
        $timestamp = strtotime($reference) ?: time();
        $weekStart = date('Y-m-d', strtotime('monday this week', $timestamp));
        $weekEnd = date('Y-m-d', strtotime('sunday this week', $timestamp));

        $appointments = Appointment::byWeek($weekStart, $weekEnd, $user);
        $grouped = [];
        foreach ($appointments as $appointment) {
            $day = date('Y-m-d', strtotime($appointment['start_at']));
            $hour = date('H:00', strtotime($appointment['start_at']));
            $grouped[$day][$hour][] = $appointment;
        }

        $days = [];
        $cursor = strtotime($weekStart);
        $end = strtotime($weekEnd);
        while ($cursor <= $end) {
            $days[] = [
                'date' => date('Y-m-d', $cursor),
                'label' => date('D d/m', $cursor),
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        $hours = [];
        for ($h = 8; $h <= 20; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        $this->view('appointments/index', [
            'title' => 'Agenda Settimanale',
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => $days,
            'hours' => $hours,
            'appointments' => $appointments,
            'groupedAppointments' => $grouped,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $clients = Client::forSelect($user);
        $stages = PipelineStage::active();
        $prefillClient = (int) $this->get('client_id', 0);
        $prefillDate = (string) $this->get('date', date('Y-m-d'));

        $this->view('appointments/form', [
            'title' => 'Nuovo Appuntamento',
            'mode' => 'create',
            'appointment' => null,
            'clients' => $clients,
            'stages' => $stages,
            'prefillClient' => $prefillClient,
            'prefillDate' => $prefillDate,
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
            $this->redirect('appointments/create');
        }

        Appointment::create($data);
        set_flash('success', 'Appuntamento creato.');
        $this->redirect('appointments');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $appointment = Appointment::findAccessible((int) $id, $user);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appuntamento non trovato.';
            return;
        }

        $clients = Client::forSelect($user);
        $stages = PipelineStage::active();
        $this->view('appointments/form', [
            'title' => 'Modifica Appuntamento',
            'mode' => 'edit',
            'appointment' => $appointment,
            'clients' => $clients,
            'stages' => $stages,
            'prefillClient' => 0,
            'prefillDate' => date('Y-m-d'),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $appointment = Appointment::findAccessible((int) $id, $user);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appuntamento non trovato.';
            return;
        }

        $data = $this->collectData($user);
        $errors = $this->validateData($data, $user);
        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('appointments/' . (int) $id . '/edit');
        }

        Appointment::update((int) $id, $data);
        set_flash('success', 'Appuntamento aggiornato.');
        $this->redirect('appointments');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $appointment = Appointment::findAccessible((int) $id, $user);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appuntamento non trovato.';
            return;
        }
        Appointment::delete((int) $id);
        set_flash('success', 'Appuntamento eliminato.');
        $this->redirect('appointments');
    }

    private function collectData(array $user): array
    {
        $stageId = (string) $this->post('stage_id', '');

        return [
            'client_id' => (int) $this->post('client_id', 0),
            'user_id' => (int) $user['id'],
            'title' => $this->resolveTitle($stageId),
            'description' => trim((string) $this->post('description', '')),
            'start_at' => $this->normalizeDateTimeInput((string) $this->post('start_at', '')),
            'end_at' => $this->normalizeDateTimeInput((string) $this->post('end_at', '')),
            'location' => trim((string) $this->post('location', '')),
            'stage_id' => $stageId,
        ];
    }

    private function validateData(array $data, array $user): array
    {
        $errors = [];
        if ($data['client_id'] <= 0) {
            $errors[] = 'Cliente obbligatorio.';
        } elseif (!Client::findAccessible($data['client_id'], $user)) {
            $errors[] = 'Cliente non accessibile.';
        }
        if ($data['stage_id'] === '') {
            $errors[] = 'Stage pipeline obbligatorio.';
        } elseif (!PipelineStage::find((int) $data['stage_id'])) {
            $errors[] = 'Stage pipeline non valido.';
        }
        $start = $this->parseDateTimeInput($data['start_at']);
        $end = $this->parseDateTimeInput($data['end_at']);
        if ($data['start_at'] === '' || $data['end_at'] === '') {
            $errors[] = 'Inizio e fine sono obbligatori.';
        } elseif ($start === null || $end === null) {
            $errors[] = 'Inizio o fine appuntamento non validi.';
        } elseif ($start >= $end) {
            $errors[] = 'Fine appuntamento deve essere successiva all\'inizio.';
        }
        return $errors;
    }

    private function resolveTitle(string $stageId): string
    {
        if ($stageId === '') {
            return 'Appuntamento';
        }

        $stage = PipelineStage::find((int) $stageId);
        if ($stage && !empty($stage['name'])) {
            return (string) $stage['name'];
        }

        return 'Appuntamento';
    }
}
