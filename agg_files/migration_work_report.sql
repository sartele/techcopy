-- ============================================================
--  TechCopy — Migrazione: aggiunta campo "Lavoro svolto"
--  Eseguire in phpMyAdmin su database "techcopy" già esistente
--  oppure: mysql -u root techcopy < migration_work_report.sql
-- ============================================================

USE techcopy;

ALTER TABLE tickets
  ADD COLUMN IF NOT EXISTS work_report TEXT DEFAULT NULL
    AFTER notes;

SELECT 'Migrazione completata ✅' AS risultato;
DESCRIBE tickets;
