<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->view('auth/login', [
            'title' => 'Login',
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrfOrFail();

        $email = trim((string) $this->post('email', ''));
        $password = (string) $this->post('password', '');

        if ($email === '' || $password === '') {
            with_old_input($_POST);
            set_flash('error', 'Email e password sono obbligatori.');
            $this->redirect('login');
        }

        if (!Auth::attempt($email, $password)) {
            with_old_input($_POST);
            set_flash('error', 'Credenziali non valide.');
            $this->redirect('login');
        }

        set_flash('success', 'Login eseguito con successo.');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrfOrFail();
        Auth::logout();
        set_flash('success', 'Logout eseguito.');
        $this->redirect('login');
    }
}

