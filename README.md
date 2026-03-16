# Mini CRM Commerciale (PHP MVC Vanilla)

MVP completo sviluppato in `./crm` con:
- autenticazione session-based (manager/seller)
- dashboard KPI vs target (settimanale/mensile) + trend grafici
- clienti con timeline unificata
- attività/contatti/mail/whatsapp (logging manuale)
- agenda settimanale stile calendar week-view
- preventivi con macro-categorie, righe, totale e upload PDF sicuro
- vendite concluse (manuale o da preventivo)
- configurazione pipeline, categorie preventivi, target venditori
- gestione utenti (solo manager)

## Struttura cartelle

```text
crm/
  app/
    Controllers/
    Core/
    Models/
    views/
  assets/
  config/
    config.example.php
    config.php
  database/
    schema.sql
    seed.sql
  public/
    index.php
    .htaccess
    assets/app.css
  storage/
    uploads/quotes/
    logs/
```

## Requisiti

- PHP 8.1+ (testato con PHP 8.3)
- MySQL 8+ / MariaDB compatibile
- Estensione PDO MySQL abilitata

## Setup rapido

1. Crea il database:
```sql
CREATE DATABASE mini_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importa schema e seed:
```bash
mysql -u root -p mini_crm < database/schema.sql
mysql -u root -p mini_crm < database/seed.sql
```

3. Configura connessione DB in `config/config.php` (o copia/edita `config/config.example.php`).

4. Avvia server locale:
```bash
cd crm
php -S localhost:8000 -t public
```

5. Apri:
`http://localhost:8000`

## Credenziali demo

- Manager: `manager@crm.local` / `crm12345`
- Seller 1: `luca@crm.local` / `crm12345`
- Seller 2: `sara@crm.local` / `crm12345`

## Sicurezza implementata (MVP)

- password hash con `password_hash` / verifica `password_verify`
- query PDO con prepared statements
- escaping output HTML con helper `e()`
- protezione CSRF su tutte le POST
- upload PDF con:
  - whitelist estensione `.pdf`
  - verifica MIME `application/pdf`
  - limite dimensione 5MB
  - salvataggio in `storage/uploads/quotes`

