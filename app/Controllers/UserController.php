<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->requireManager();
        $users = User::all();
        $this->view('users/index', [
            'title' => 'Utenti',
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        $this->requireManager();
        $this->verifyCsrfOrFail();

        $name = trim((string) $this->post('name', ''));
        $email = trim((string) $this->post('email', ''));
        $password = (string) $this->post('password', '');
        $role = (string) $this->post('role', 'seller');

        $errors = [];
        if ($name === '') {
            $errors[] = 'Nome obbligatorio.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email non valida.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password minima 6 caratteri.';
        }
        if (!in_array($role, ['manager', 'seller'], true)) {
            $errors[] = 'Ruolo non valido.';
        }

        if ($errors !== []) {
            set_flash('error', implode(' ', $errors));
            $this->redirect('users');
        }

        if (User::findByEmail($email)) {
            set_flash('error', 'Esiste già un utente con questa email.');
            $this->redirect('users');
        }

        User::create($name, $email, $password, $role);
        set_flash('success', 'Utente creato.');
        $this->redirect('users');
    }
}

