<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Client;
use App\Models\User;

final class ClientController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $filters = [
            'q' => trim((string) $this->get('q', '')),
            'owner_user_id' => (string) $this->get('owner_user_id', ''),
        ];

        $clients = Client::search($filters, $user);
        $owners = User::sellers();

        $this->view('clients/index', [
            'title' => 'Aziende / Clienti',
            'clients' => $clients,
            'filters' => $filters,
            'owners' => $owners,
            'currentUser' => $user,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $owners = User::sellers();

        $this->view('clients/form', [
            'title' => 'Nuovo Cliente',
            'mode' => 'create',
            'client' => null,
            'owners' => $owners,
            'currentUser' => $user,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();

        $data = $this->collectClientData($user);
        $errors = $this->validateClientData($data);
        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('clients/create');
        }

        Client::create($data);
        set_flash('success', 'Cliente creato correttamente.');
        $this->redirect('clients');
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $client = Client::findAccessible((int) $id, $user);
        if (!$client) {
            http_response_code(404);
            echo 'Cliente non trovato.';
            return;
        }

        $timeline = Client::timeline((int) $id, $user);
        $this->view('clients/show', [
            'title' => 'Dettaglio Cliente',
            'client' => $client,
            'timeline' => $timeline,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $client = Client::findAccessible((int) $id, $user);
        if (!$client) {
            http_response_code(404);
            echo 'Cliente non trovato.';
            return;
        }
        if ($user['role'] === 'seller' && (int) $client['owner_user_id'] !== (int) $user['id']) {
            http_response_code(403);
            echo 'Puoi modificare solo i tuoi clienti.';
            return;
        }

        $owners = User::sellers();
        $this->view('clients/form', [
            'title' => 'Modifica Cliente',
            'mode' => 'edit',
            'client' => $client,
            'owners' => $owners,
            'currentUser' => $user,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();

        $existing = Client::findAccessible((int) $id, $user);
        if (!$existing) {
            http_response_code(404);
            echo 'Cliente non trovato.';
            return;
        }
        if ($user['role'] === 'seller' && (int) $existing['owner_user_id'] !== (int) $user['id']) {
            http_response_code(403);
            echo 'Puoi modificare solo i tuoi clienti.';
            return;
        }

        $data = $this->collectClientData($user);
        $errors = $this->validateClientData($data);
        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('clients/' . (int) $id . '/edit');
        }

        Client::update((int) $id, $data);
        set_flash('success', 'Cliente aggiornato.');
        $this->redirect('clients');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();

        $client = Client::findAccessible((int) $id, $user);
        if (!$client) {
            http_response_code(404);
            echo 'Cliente non trovato.';
            return;
        }

        if ($user['role'] === 'seller' && (int) $client['owner_user_id'] !== (int) $user['id']) {
            http_response_code(403);
            echo 'Non puoi eliminare questo cliente.';
            return;
        }

        Client::delete((int) $id);
        set_flash('success', 'Cliente eliminato.');
        $this->redirect('clients');
    }

    private function collectClientData(array $user): array
    {
        $ownerId = (int) $this->post('owner_user_id', $user['id']);
        if ($user['role'] === 'seller') {
            $ownerId = (int) $user['id'];
        }

        return [
            'company_name' => trim((string) $this->post('company_name', '')),
            'contact_name' => trim((string) $this->post('contact_name', '')),
            'email' => trim((string) $this->post('email', '')),
            'phone' => trim((string) $this->post('phone', '')),
            'address' => trim((string) $this->post('address', '')),
            'website' => trim((string) $this->post('website', '')),
            'notes' => trim((string) $this->post('notes', '')),
            'owner_user_id' => $ownerId,
            'is_shared' => $this->post('is_shared', 0),
        ];
    }

    private function validateClientData(array $data): array
    {
        $errors = [];
        if ($data['company_name'] === '') {
            $errors[] = 'Nome azienda obbligatorio.';
        }
        if ($data['contact_name'] === '') {
            $errors[] = 'Referente obbligatorio.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email non valida.';
        }
        if ($data['website'] !== '' && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Sito web non valido.';
        }
        return $errors;
    }
}
