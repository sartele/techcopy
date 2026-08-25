-- ============================================================
--  TechCopy — Migrazione: sistema notifiche email
--  Eseguire in phpMyAdmin su database "techcopy" già esistente
-- ============================================================

USE techcopy;

-- Aggiunge colonna preferenza notifiche per ogni utente
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS notify_email TINYINT(1) NOT NULL DEFAULT 1
    AFTER color;

-- Di default tutti gli utenti attivi ricevono le notifiche
UPDATE users SET notify_email = 1 WHERE active = 1;

SELECT CONCAT('Migrazione completata — ', COUNT(*), ' utenti con notify_email=1') AS risultato
FROM users WHERE notify_email = 1;
