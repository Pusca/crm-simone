<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteCategory;

final class QuoteController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $filters = [
            'q' => trim((string) $this->get('q', '')),
            'status' => (string) $this->get('status', ''),
            'client_id' => (string) $this->get('client_id', ''),
        ];

        $quotes = Quote::search($filters, $user);
        $clients = Client::forSelect($user);

        $this->view('quotes/index', [
            'title' => 'Preventivi',
            'quotes' => $quotes,
            'filters' => $filters,
            'clients' => $clients,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $clients = Client::forSelect($user);
        $categories = QuoteCategory::active();
        $activityId = (int) $this->get('activity_id', 0);
        $clientId = (int) $this->get('client_id', 0);

        if ($activityId > 0) {
            $activity = Activity::findAccessible($activityId, $user);
            if ($activity) {
                $clientId = (int) $activity['client_id'];
            } else {
                $activityId = 0;
            }
        }

        $this->view('quotes/form', [
            'title' => 'Nuovo Preventivo',
            'mode' => 'create',
            'quote' => null,
            'clients' => $clients,
            'categories' => $categories,
            'prefillClient' => $clientId,
            'prefillActivity' => $activityId,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();

        $data = $this->collectData($user);
        $items = $this->collectItems();

        $errors = $this->validateData($data, $items, $user);
        $pdfPath = null;
        if ($errors === []) {
            [$ok, $message, $storedPath] = $this->handlePdfUpload($_FILES['pdf_file'] ?? null);
            if (!$ok) {
                $errors[] = $message;
            } else {
                $pdfPath = $storedPath;
            }
        }

        if ($errors !== []) {
            with_old_input($_POST);
            set_flash('error', implode(' ', $errors));
            $this->redirect('quotes/create');
        }

        $data['pdf_path'] = $pdfPath;
        $data['amount'] = array_sum(array_map(static fn(array $i): float => (float) $i['amount'], $items));

        $quoteId = Quote::createWithItems($data, $items);
        set_flash('success', 'Preventivo creato.');
        $this->redirect('quotes/' . $quoteId);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $quote = Quote::findAccessible((int) $id, $user);
        if (!$quote) {
            http_response_code(404);
            echo 'Preventivo non trovato.';
            return;
        }
        $items = Quote::items((int) $id);

        $this->view('quotes/show', [
            'title' => 'Dettaglio Preventivo',
            'quote' => $quote,
            'items' => $items,
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail();
        $user = Auth::user();
        $quote = Quote::findAccessible((int) $id, $user);
        if (!$quote) {
            http_response_code(404);
            echo 'Preventivo non trovato.';
            return;
        }

        $status = (string) $this->post('status', 'draft');
        if (!in_array($status, ['draft', 'sent', 'won', 'lost'], true)) {
            set_flash('error', 'Status non valido.');
            $this->redirect('quotes/' . (int) $id);
        }

        Quote::updateStatus((int) $id, $status);
        set_flash('success', 'Stato preventivo aggiornato.');
        $this->redirect('quotes/' . (int) $id);
    }

    public function downloadPdf(string $id): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $quote = Quote::findAccessible((int) $id, $user);
        if (!$quote || empty($quote['pdf_path'])) {
            http_response_code(404);
            echo 'PDF non trovato.';
            return;
        }

        $relative = ltrim((string) $quote['pdf_path'], '/\\');
        $fullPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if (!is_file($fullPath)) {
            http_response_code(404);
            echo 'File PDF non disponibile.';
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
    }

    private function collectData(array $user): array
    {
        return [
            'client_id' => (int) $this->post('client_id', 0),
            'user_id' => (int) $user['id'],
            'source_activity_id' => (string) $this->post('source_activity_id', ''),
            'title' => trim((string) $this->post('title', '')),
            'description' => trim((string) $this->post('description', '')),
            'amount' => 0.0,
            'status' => (string) $this->post('status', 'draft'),
            'sent_at' => (string) $this->post('sent_at', ''),
            'pdf_path' => null,
        ];
    }

    private function collectItems(): array
    {
        $categories = $_POST['item_category_id'] ?? [];
        $descriptions = $_POST['item_description'] ?? [];
        $amounts = $_POST['item_amount'] ?? [];

        $items = [];
        $count = max(count($categories), count($descriptions), count($amounts));
        for ($i = 0; $i < $count; $i++) {
            $categoryId = isset($categories[$i]) ? (int) $categories[$i] : 0;
            $description = isset($descriptions[$i]) ? trim((string) $descriptions[$i]) : '';
            $amount = isset($amounts[$i]) ? (float) $amounts[$i] : 0.0;
            if ($categoryId > 0 && $amount >= 0) {
                $items[] = [
                    'category_id' => $categoryId,
                    'description' => $description,
                    'amount' => $amount,
                ];
            }
        }
        return $items;
    }

    private function validateData(array $data, array $items, array $user): array
    {
        $errors = [];
        if ($data['client_id'] <= 0) {
            $errors[] = 'Cliente obbligatorio.';
        } elseif (!Client::findAccessible($data['client_id'], $user)) {
            $errors[] = 'Cliente non accessibile.';
        }
        if ($data['source_activity_id'] !== '') {
            $activity = Activity::findAccessible((int) $data['source_activity_id'], $user);
            if (!$activity) {
                $errors[] = 'Attività sorgente non accessibile.';
            }
        }
        if ($data['title'] === '') {
            $errors[] = 'Titolo preventivo obbligatorio.';
        }
        if (!in_array($data['status'], ['draft', 'sent', 'won', 'lost'], true)) {
            $errors[] = 'Status preventivo non valido.';
        }
        if ($data['status'] === 'sent' && $data['sent_at'] === '') {
            $errors[] = 'Data invio obbligatoria se stato è sent.';
        }
        if (count($items) === 0) {
            $errors[] = 'Inserisci almeno una riga con categoria e importo.';
        }
        return $errors;
    }

    private function handlePdfUpload(?array $file): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [true, '', null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [false, 'Upload PDF fallito.', null];
        }

        $maxBytes = (int) config('security.max_upload_bytes');
        if (($file['size'] ?? 0) > $maxBytes) {
            return [false, 'PDF troppo grande. Max 5MB.', null];
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return [false, 'Solo file PDF consentiti.', null];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!in_array($mime, (array) config('security.allowed_pdf_mime'), true)) {
            return [false, 'Mime type non valido per PDF.', null];
        }

        $uploadDir = (string) config('paths.quote_uploads');
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [false, 'Impossibile creare cartella upload.', null];
        }

        $fileName = 'quote_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $target = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return [false, 'Impossibile salvare il PDF.', null];
        }

        $relative = 'storage/uploads/quotes/' . $fileName;
        return [true, '', $relative];
    }
}
