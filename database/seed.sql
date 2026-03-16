-- Demo seed data
SET NAMES utf8mb4;

INSERT INTO users (id, name, email, password_hash, role, created_at) VALUES
(1, 'Mario Manager', 'manager@crm.local', '$2y$10$iMaLWZRWaTKQ8JC28VePIe7xUe7o8ydNvGGlT.oCghMhYEnyfjW4a', 'manager', '2026-01-01 09:00:00'),
(2, 'Luca Seller', 'luca@crm.local', '$2y$10$iMaLWZRWaTKQ8JC28VePIe7xUe7o8ydNvGGlT.oCghMhYEnyfjW4a', 'seller', '2026-01-01 09:10:00'),
(3, 'Sara Seller', 'sara@crm.local', '$2y$10$iMaLWZRWaTKQ8JC28VePIe7xUe7o8ydNvGGlT.oCghMhYEnyfjW4a', 'seller', '2026-01-01 09:20:00');

INSERT INTO pipeline_stages (id, name, sort_order, is_active, created_at) VALUES
(1, 'Telefonata', 10, 1, '2026-01-01 10:00:00'),
(2, 'Primo Appuntamento', 20, 1, '2026-01-01 10:00:00'),
(3, 'Secondo Appuntamento', 30, 1, '2026-01-01 10:00:00'),
(4, 'Preventivo Inviato', 40, 1, '2026-01-01 10:00:00'),
(5, 'Negoziazione', 50, 1, '2026-01-01 10:00:00');

INSERT INTO quote_categories (id, name, sort_order, is_active, created_at) VALUES
(1, 'Software', 10, 1, '2026-01-01 10:00:00'),
(2, 'Consulenza', 20, 1, '2026-01-01 10:00:00'),
(3, 'Setup Iniziale', 30, 1, '2026-01-01 10:00:00'),
(4, 'Formazione', 40, 1, '2026-01-01 10:00:00'),
(5, 'Assistenza', 50, 1, '2026-01-01 10:00:00');

INSERT INTO clients (id, company_name, contact_name, email, phone, address, website, notes, owner_user_id, is_shared, created_at) VALUES
(1, 'Alfa Srl', 'Gianni Riva', 'gianni@alfa.it', '+39 02111111', 'Via Roma 1, Milano', 'https://alfa.example', 'Cliente enterprise', 2, 0, '2026-02-01 09:00:00'),
(2, 'Beta Consulting', 'Elena Fontana', 'elena@beta.it', '+39 02111222', 'Via Torino 12, Torino', 'https://beta.example', 'Interessata modulo vendite', 2, 1, '2026-02-02 10:00:00'),
(3, 'Gamma Tech', 'Paolo Neri', 'paolo@gamma.it', '+39 02111333', 'Via Marconi 4, Bologna', 'https://gamma.example', 'Lead caldo', 2, 0, '2026-02-03 11:00:00'),
(4, 'Delta Medical', 'Ilaria Bruni', 'ilaria@delta.it', '+39 02111444', 'Via Diaz 7, Verona', 'https://delta.example', 'Richiesta integrazione ERP', 2, 0, '2026-02-04 11:30:00'),
(5, 'Epsilon Food', 'Marco Sala', 'marco@epsilon.it', '+39 02111555', 'Corso Italia 9, Parma', 'https://epsilon.example', 'Stagionalita elevata', 2, 1, '2026-02-05 09:20:00'),
(6, 'Zeta Logistics', 'Chiara Lodi', 'chiara@zeta.it', '+39 02111666', 'Via Cargo 2, Genova', 'https://zeta.example', 'Interesse dashboard KPI', 3, 0, '2026-02-06 09:50:00'),
(7, 'Eta Retail', 'Roberto De Luca', 'roberto@eta.it', '+39 02111777', 'Via Po 55, Firenze', 'https://eta.example', 'Molte sedi', 3, 0, '2026-02-07 10:15:00'),
(8, 'Theta Energy', 'Francesca Longhi', 'francesca@theta.it', '+39 02111888', 'Via Volta 20, Roma', 'https://theta.example', 'Budget Q2 approvato', 3, 0, '2026-02-08 08:45:00'),
(9, 'Iota Labs', 'Lorenzo Serra', 'lorenzo@iota.it', '+39 02111999', 'Via Copernico 3, Pisa', 'https://iota.example', 'Demo tecnica completata', 3, 1, '2026-02-09 09:40:00'),
(10, 'Kappa Design', 'Marta Greco', 'marta@kappa.it', '+39 02112000', 'Via Canova 14, Venezia', 'https://kappa.example', 'Richiesta offerta rapida', 3, 0, '2026-02-10 10:30:00');

INSERT INTO activities (id, client_id, user_id, type, stage_id, subject, body, occurred_at, next_action_at, created_at) VALUES
(1, 1, 2, 'call', 1, 'Primo contatto telefonico', 'Presentazione proposta CRM.', '2026-02-20 09:30:00', '2026-02-22 10:00:00', '2026-02-20 09:40:00'),
(2, 1, 2, 'email', 2, 'Invio brochure', 'Inviata brochure con feature principali.', '2026-02-22 10:05:00', '2026-02-24 15:00:00', '2026-02-22 10:10:00'),
(3, 2, 2, 'whatsapp', 1, 'Messaggio follow-up', 'Conferma interesse per demo.', '2026-02-23 12:00:00', '2026-02-25 09:00:00', '2026-02-23 12:01:00'),
(4, 3, 2, 'meeting', 2, 'Demo online', 'Demo piattaforma con team commerciale.', '2026-02-24 11:00:00', '2026-02-26 16:00:00', '2026-02-24 12:15:00'),
(5, 4, 2, 'call', 1, 'Scoperta esigenze', 'Capire processo pipeline interno.', '2026-02-25 10:00:00', '2026-02-27 10:30:00', '2026-02-25 10:05:00'),
(6, 5, 2, 'email', 4, 'Invio preventivo', 'Preventivo versione base inviato.', '2026-02-26 14:00:00', '2026-03-01 09:30:00', '2026-02-26 14:01:00'),
(7, 6, 3, 'call', 1, 'Primo contatto logistico', 'Identificate criticita su reportistica.', '2026-02-20 11:30:00', '2026-02-23 10:00:00', '2026-02-20 11:35:00'),
(8, 6, 3, 'meeting', 2, 'Appuntamento in sede', 'Raccolta requisiti operativi.', '2026-02-23 10:30:00', '2026-02-27 10:00:00', '2026-02-23 12:00:00'),
(9, 7, 3, 'email', 1, 'Mail introduttiva', 'Inviato deck retail multi-store.', '2026-02-24 09:20:00', '2026-02-26 09:00:00', '2026-02-24 09:22:00'),
(10, 8, 3, 'meeting', 3, 'Secondo incontro', 'Allineamento tecnico con IT.', '2026-02-25 16:00:00', '2026-02-28 11:00:00', '2026-02-25 17:00:00'),
(11, 9, 3, 'whatsapp', 4, 'Invio offerta', 'Offerta inviata e ricevuta.', '2026-02-26 15:30:00', '2026-03-02 10:00:00', '2026-02-26 15:31:00'),
(12, 10, 3, 'call', 1, 'Contatto iniziale design', 'Richiesta meeting rapido.', '2026-02-27 09:15:00', '2026-03-03 11:00:00', '2026-02-27 09:20:00'),
(13, 2, 2, 'meeting', 2, 'Meeting commerciale', 'Demo completa con decision maker.', '2026-03-02 10:00:00', '2026-03-04 09:30:00', '2026-03-02 11:00:00'),
(14, 3, 2, 'call', 5, 'Negoziazione prezzo', 'Discussa scontistica trimestrale.', '2026-03-02 15:00:00', '2026-03-05 10:00:00', '2026-03-02 15:10:00'),
(15, 6, 3, 'meeting', 3, 'Workshop operativo', 'Validato scope progetto.', '2026-03-03 09:00:00', '2026-03-04 16:00:00', '2026-03-03 11:30:00'),
(16, 8, 3, 'email', 4, 'Invio proposta finale', 'Proposta enterprise inviata.', '2026-03-03 12:30:00', '2026-03-06 09:00:00', '2026-03-03 12:35:00');

INSERT INTO appointments (id, client_id, user_id, title, description, start_at, end_at, location, stage_id, created_at) VALUES
(1, 1, 2, 'Call di allineamento', 'Definizione obiettivi Q2', '2026-03-02 09:00:00', '2026-03-02 10:00:00', 'Google Meet', 2, '2026-02-28 09:00:00'),
(2, 2, 2, 'Demo commerciale', 'Presentazione modulo vendite', '2026-03-03 11:00:00', '2026-03-03 12:00:00', 'Google Meet', 2, '2026-03-01 11:00:00'),
(3, 3, 2, 'Negoziazione economica', 'Discussione contratto annuale', '2026-03-04 15:00:00', '2026-03-04 16:00:00', 'Milano HQ', 5, '2026-03-01 15:00:00'),
(4, 4, 2, 'Raccolta requisiti', 'Focus su integrazione ERP', '2026-03-05 10:00:00', '2026-03-05 11:30:00', 'Teams', 2, '2026-03-01 10:00:00'),
(5, 5, 2, 'Follow-up preventivo', 'Revisione condizioni economiche', '2026-03-06 14:00:00', '2026-03-06 15:00:00', 'Telefonico', 4, '2026-03-01 14:00:00'),
(6, 6, 3, 'Meeting logistico', 'Piano rollout sedi', '2026-03-02 14:00:00', '2026-03-02 15:30:00', 'Genova', 3, '2026-03-01 14:00:00'),
(7, 7, 3, 'Demo retail', 'Focus report vendite', '2026-03-03 10:00:00', '2026-03-03 11:00:00', 'Google Meet', 2, '2026-03-01 10:00:00'),
(8, 8, 3, 'Comitato decisionale', 'Presentazione ROI', '2026-03-04 09:30:00', '2026-03-04 11:00:00', 'Roma', 5, '2026-03-01 09:00:00'),
(9, 9, 3, 'Revisione offerta', 'Analisi condizioni finali', '2026-03-05 16:00:00', '2026-03-05 17:00:00', 'Teams', 4, '2026-03-01 16:00:00'),
(10, 10, 3, 'Kickoff trattativa', 'Allineamento processi', '2026-03-06 10:00:00', '2026-03-06 11:00:00', 'Venezia', 2, '2026-03-01 10:00:00');

INSERT INTO quotes (id, client_id, user_id, source_activity_id, title, description, amount, status, created_at, sent_at, pdf_path) VALUES
(1, 1, 2, 2, 'Offerta CRM Alfa Base', 'Licenze + setup base', 8500.00, 'sent', '2026-02-24 16:00:00', '2026-02-25', NULL),
(2, 2, 2, 13, 'Offerta Beta Pro', 'Pacchetto pro annuale', 12900.00, 'draft', '2026-03-02 18:00:00', NULL, NULL),
(3, 3, 2, 14, 'Offerta Gamma Enterprise', 'Suite enterprise con training', 21500.00, 'sent', '2026-03-02 18:30:00', '2026-03-03', NULL),
(4, 5, 2, 6, 'Rinnovo Epsilon', 'Upgrade modulo dashboard', 7400.00, 'won', '2026-02-26 16:10:00', '2026-02-26', NULL),
(5, 6, 3, 8, 'Proposta Zeta', 'Implementazione multi-sede', 15400.00, 'sent', '2026-02-27 17:00:00', '2026-02-27', NULL),
(6, 8, 3, 16, 'Proposta Theta Enterprise', 'Bundle enterprise + assistenza', 26800.00, 'draft', '2026-03-03 13:10:00', NULL, NULL),
(7, 9, 3, 11, 'Offerta Iota Labs', 'Licenze + consulenza analitica', 9800.00, 'won', '2026-02-26 18:00:00', '2026-02-27', NULL),
(8, 10, 3, 12, 'Offerta Kappa Design', 'Pacchetto startup', 5200.00, 'lost', '2026-03-01 10:40:00', '2026-03-01', NULL);

INSERT INTO quote_items (quote_id, category_id, description, amount) VALUES
(1, 1, 'Licenze 20 utenti', 5000.00),
(1, 3, 'Setup iniziale', 2000.00),
(1, 4, 'Formazione team', 1500.00),
(2, 1, 'Licenze pro', 7500.00),
(2, 2, 'Consulenza processo', 3200.00),
(2, 5, 'Assistenza annuale', 2200.00),
(3, 1, 'Licenze enterprise', 12000.00),
(3, 2, 'Consulenza avanzata', 5500.00),
(3, 4, 'Formazione on-site', 4000.00),
(4, 1, 'Upgrade modulo', 4200.00),
(4, 5, 'Assistenza premium', 3200.00),
(5, 1, 'Licenze multi-sede', 9800.00),
(5, 2, 'Consulenza rollout', 3600.00),
(5, 3, 'Setup', 2000.00),
(6, 1, 'Licenze enterprise', 17000.00),
(6, 2, 'Consulenza integrazione', 5400.00),
(6, 5, 'Assistenza 24/7', 4400.00),
(7, 1, 'Licenze analytics', 5600.00),
(7, 2, 'Consulenza BI', 2900.00),
(7, 4, 'Training power users', 1300.00),
(8, 1, 'Licenze base', 3200.00),
(8, 3, 'Setup startup', 1200.00),
(8, 5, 'Assistenza standard', 800.00);

INSERT INTO sales (id, client_id, user_id, quote_id, title, amount, closed_at, notes, created_at) VALUES
(1, 5, 2, 4, 'Chiusura Epsilon upgrade', 7400.00, '2026-03-01', 'Contratto annuale firmato.', '2026-03-01 16:00:00'),
(2, 9, 3, 7, 'Chiusura Iota analytics', 9800.00, '2026-03-02', 'Pagamenti in due tranche.', '2026-03-02 17:30:00'),
(3, 1, 2, 1, 'Upsell Alfa modulo report', 3600.00, '2026-02-28', 'Vendita extra da trattativa precedente.', '2026-02-28 12:00:00'),
(4, 7, 3, NULL, 'Servizio onboarding Eta', 4200.00, '2026-02-27', 'Vendita manuale da contatto diretto.', '2026-02-27 11:20:00'),
(5, 6, 3, 5, 'Acconto progetto Zeta', 6000.00, '2026-03-03', 'Avviata fase 1.', '2026-03-03 18:00:00');

INSERT INTO targets (user_id, period, metric, target_value, start_date, end_date, created_at) VALUES
(2, 'weekly', 'appointments', 8, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(2, 'weekly', 'quotes', 5, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(2, 'weekly', 'sales', 3, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(2, 'monthly', 'appointments', 30, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00'),
(2, 'monthly', 'quotes', 18, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00'),
(2, 'monthly', 'sales', 10, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00'),
(3, 'weekly', 'appointments', 9, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(3, 'weekly', 'quotes', 6, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(3, 'weekly', 'sales', 4, '2026-03-02', '2026-03-08', '2026-03-01 09:00:00'),
(3, 'monthly', 'appointments', 32, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00'),
(3, 'monthly', 'quotes', 20, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00'),
(3, 'monthly', 'sales', 12, '2026-03-01', '2026-03-31', '2026-03-01 09:00:00');

