<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Sale;

final class SaleController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $filters = [
            'q' => trim((string) $this->get('q', '')),
            'client_id' => (string) $this->get('client_id', ''),
            'date_from' => (string) $this->get('date_from', ''),
            'date_to' => (string) $this->get('date_to', ''),
        ];

        $sales = Sale::search($filters, $user);
        $clients = Client::forSelect($user);

        $this->view('sales/index', [
            'title' => 'Vendite Concluse',
            'sales' => $sales,
            'filters' => $filters,
            'clients' => $clients,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $clients = Client::forSelect($user);
        $quoteId = (int) $this->get('quote_id', 0);
        $sourceQuote = null;
        if ($quoteId > 0) {
            $sourceQuote = Quote::findAccessible($quoteId, $user);
            if (!$sourceQuote) {
                $quoteId = 0;
            }
        }

        $this->view('sales/form', [
            'title' => 'Nuova Vendita',
            'clients' => $clients,
            'sourceQuote' => $sourceQuote,
            'prefillQuoteId' => $quoteId,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $data = [
            'client_id' => (int) $this->post('client_id', 0),
            'user_id' => (int) $user['id'],
            'quote_id' => (string) $this->post('quote_id', ''),
            'title' => trim((string) $this->post('title', '')),
            'amount' => (float) $this->post('amount', 0),
            'closed_at' => $this->normalizeDateInput((string) $this->post('closed_at', date('Y-m-d'))),
            'notes' => trim((string) $this->post('notes', '')),
        ];

        $errors = [];
        if ($data['client_id'] <= 0 || !Client::findAccessible($data['client_id'], $user)) {
            $errors[] = 'Cliente non valido.';
        }
        if ($data['title'] === '') {
            $errors[] = 'Titolo vendita obbligatorio.';
        }
        if ($data['amount'] <= 0) {
            $errors[] = 'Importo vendita deve essere > 0.';
        }
        if ($data['closed_at'] === '' || $this->parseDateInput($data['closed_at']) === null) {
            $errors[] = 'Data chiusura non valida.';
        }

        $quote = null;
        if ($data['quote_id'] !== '') {
            $quote = Quote::findAccessible((int) $data['quote_id'], $user);
            if (!$quote) {
                $errors[] = 'Preventivo associato non accessibile.';
            } elseif ((int) $quote['client_id'] !== (int) $data['client_id']) {
                $errors[] = 'Il preventivo associato non appartiene al cliente selezionato.';
            }
        }

        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('sales/create');
        }

        Sale::create($data);
        if ($quote) {
            Quote::updateStatus((int) $data['quote_id'], 'won');
        }

        set_flash('success', 'Vendita registrata.');
        $this->redirect('sales');
    }
}
