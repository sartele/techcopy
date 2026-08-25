-- ============================================================
--  TechCopy — Migrazione: più tecnici per intervento
--  Eseguire in phpMyAdmin su database "techcopy" già esistente
--  oppure: mysql -u root techcopy < migration_multi_tecnici.sql
-- ============================================================

USE techcopy;

-- Nuova tabella relazione N:N ticket ↔ utenti
CREATE TABLE IF NOT EXISTS ticket_users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT          NOT NULL,
    user_id    INT          NOT NULL,
    role_note  VARCHAR(100) DEFAULT NULL,
    added_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ticket_user (ticket_id, user_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- Popola ticket_users dai tech_id già esistenti (migrazione dati)
INSERT IGNORE INTO ticket_users (ticket_id, user_id, role_note)
SELECT id, tech_id, 'Responsabile'
FROM tickets
WHERE tech_id IS NOT NULL;

SELECT CONCAT('Migrazione completata — ', COUNT(*), ' assegnazioni create') AS risultato
FROM ticket_users;
