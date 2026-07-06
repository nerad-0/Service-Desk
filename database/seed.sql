SET NAMES utf8mb4;

INSERT INTO roles (id, name, label) VALUES
    (1, 'USER', 'Běžný uživatel'),
    (2, 'TECHNICIAN', 'IT technik'),
    (3, 'ADMIN', 'Administrátor');

INSERT INTO users (id, role_id, name, email, password_hash, phone, department, is_active) VALUES
    (1, 3, 'Admin ServisDesk', 'admin@servisdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+420 111 222 333', 'IT oddělení', 1),
    (2, 2, 'Technik Jan Novák', 'technik@servisdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+420 222 333 444', 'IT oddělení', 1),
    (3, 1, 'Studentka Eva Malá', 'student@servisdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '4.IT', 1),
    (4, 1, 'Učitel Petr Svoboda', 'ucitel@servisdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+420 333 444 555', 'Matematika', 1);

INSERT INTO categories (id, name, description, color, is_active) VALUES
    (1, 'Hardware', 'Počítače, monitory, projektory, tiskárny a další fyzická zařízení.', '#0f766e', 1),
    (2, 'Software', 'Instalace aplikací, licence, aktualizace a chyby programů.', '#7c3aed', 1),
    (3, 'Síť a internet', 'Wi-Fi, kabelová síť, VPN a přístup k internetu.', '#2563eb', 1),
    (4, 'Účty a přístupy', 'Přihlašování, hesla, školní účty a oprávnění.', '#c2410c', 1);

INSERT INTO tickets (id, category_id, author_id, assigned_to, title, description, status, priority, created_at, updated_at, resolved_at, closed_at) VALUES
    (1, 1, 4, 2, 'Projektor v učebně 204 se nezapne', 'Po zapnutí projektoru bliká kontrolka, ale obraz se nezobrazí. Problém trvá od pondělí.', 'in_progress', 'high', '2026-06-20 08:20:00', '2026-06-20 09:10:00', NULL, NULL),
    (2, 3, 3, 2, 'Slabý signál Wi-Fi ve třetím patře', 'V učebně 312 často vypadává školní Wi-Fi, hlavně během dopoledne.', 'open', 'medium', '2026-06-21 10:15:00', '2026-06-21 10:15:00', NULL, NULL),
    (3, 4, 3, NULL, 'Nejde se přihlásit do školního portálu', 'Po zadání hesla se zobrazí hláška, že účet je dočasně uzamčen.', 'new', 'urgent', '2026-06-22 07:50:00', '2026-06-22 07:50:00', NULL, NULL),
    (4, 2, 4, 2, 'Chybí aktualizace kancelářského balíku', 'V kabinetu matematiky je stará verze kancelářského balíku a nejdou otevřít nové dokumenty.', 'resolved', 'medium', '2026-06-15 12:00:00', '2026-06-16 14:30:00', '2026-06-16 14:30:00', NULL),
    (5, 1, 3, 2, 'Tiskárna v knihovně netiskne oboustranně', 'Tiskárna tiskne pouze jednostranně, i když je v nastavení vybrán duplex.', 'closed', 'low', '2026-06-10 09:45:00', '2026-06-12 11:00:00', '2026-06-11 15:20:00', '2026-06-12 11:00:00');

INSERT INTO ticket_comments (ticket_id, author_id, body, is_internal, created_at) VALUES
    (1, 4, 'Projektor jsem zkoušel zapnout i přes ovladač, ale výsledek je stejný.', 0, '2026-06-20 08:22:00'),
    (1, 2, 'Zkontroluji napájení a lampu během druhé vyučovací hodiny.', 0, '2026-06-20 09:10:00'),
    (1, 2, 'Interní poznámka: pravděpodobně vadný zdroj, ověřit náhradní kabel.', 1, '2026-06-20 09:12:00'),
    (2, 3, 'Problém se objevuje hlavně při připojení celé třídy.', 0, '2026-06-21 10:20:00'),
    (4, 2, 'Aktualizace byla provedena, prosím o ověření otevření dokumentů.', 0, '2026-06-16 14:30:00'),
    (5, 2, 'Nastavení duplexu bylo opraveno v ovladači tiskárny.', 0, '2026-06-11 15:20:00');

INSERT INTO ticket_status_history (ticket_id, changed_by, old_status, new_status, note, created_at) VALUES
    (1, 4, NULL, 'new', 'Požadavek vytvořen.', '2026-06-20 08:20:00'),
    (1, 2, 'new', 'in_progress', 'Technik začal požadavek řešit.', '2026-06-20 09:10:00'),
    (2, 3, NULL, 'new', 'Požadavek vytvořen.', '2026-06-21 10:15:00'),
    (2, 2, 'new', 'open', 'Požadavek převzat do fronty.', '2026-06-21 10:35:00'),
    (3, 3, NULL, 'new', 'Požadavek vytvořen.', '2026-06-22 07:50:00'),
    (4, 4, NULL, 'new', 'Požadavek vytvořen.', '2026-06-15 12:00:00'),
    (4, 2, 'new', 'resolved', 'Aktualizace dokončena.', '2026-06-16 14:30:00'),
    (5, 3, NULL, 'new', 'Požadavek vytvořen.', '2026-06-10 09:45:00'),
    (5, 2, 'new', 'resolved', 'Opraveno nastavení ovladače.', '2026-06-11 15:20:00'),
    (5, 3, 'resolved', 'closed', 'Uživatel potvrdil řešení.', '2026-06-12 11:00:00');

INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, metadata, ip_address, created_at) VALUES
    (1, 'seed.database_loaded', 'system', NULL, JSON_OBJECT('source', 'seed.sql'), '127.0.0.1', NOW()),
    (2, 'ticket.assigned', 'ticket', 1, JSON_OBJECT('assigned_to', 2), '127.0.0.1', '2026-06-20 09:10:00'),
    (2, 'ticket.status_changed', 'ticket', 4, JSON_OBJECT('old_status', 'new', 'new_status', 'resolved'), '127.0.0.1', '2026-06-16 14:30:00');

