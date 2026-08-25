-- ============================================================
--  TechCopy — Schema Database MySQL
--  Importare in phpMyAdmin o tramite: mysql -u root < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS techcopy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techcopy;

-- ============================================================
-- UTENTI
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hash
    role        ENUM('admin','supervisor','tech','viewer') NOT NULL DEFAULT 'tech',
    avatar      VARCHAR(4) NOT NULL DEFAULT 'US',
    color        VARCHAR(10) NOT NULL DEFAULT '#00d4ff',
    notify_email TINYINT(1) NOT NULL DEFAULT 1,
    active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- CLIENTI
-- ============================================================
CREATE TABLE IF NOT EXISTS clients (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    contact     VARCHAR(100),
    phone       VARCHAR(30),
    email       VARCHAR(150),
    address     VARCHAR(255),
    city        VARCHAR(100),
    zip         VARCHAR(10),
    lat         DECIMAL(10,7) DEFAULT NULL,
    lng         DECIMAL(10,7) DEFAULT NULL,
    notes       TEXT,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- STAMPANTI / FOTOCOPIATORI
-- ============================================================
CREATE TABLE IF NOT EXISTS printers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    client_id       INT NOT NULL,
    brand           VARCHAR(80) NOT NULL,
    model           VARCHAR(100) NOT NULL,
    serial          VARCHAR(80) NOT NULL,
    type            ENUM('color','bw') NOT NULL DEFAULT 'bw',
    location        VARCHAR(150),
    has_adf         TINYINT(1) NOT NULL DEFAULT 0,
    has_duplex      TINYINT(1) NOT NULL DEFAULT 0,
    has_fax         TINYINT(1) NOT NULL DEFAULT 0,
    has_scan        TINYINT(1) NOT NULL DEFAULT 0,
    counter_bw      BIGINT NOT NULL DEFAULT 0,
    counter_color   BIGINT NOT NULL DEFAULT 0,
    purchase_date   DATE DEFAULT NULL,
    warranty_exp    DATE DEFAULT NULL,
    notes           TEXT,
    active          TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INTERVENTI / TICKET
-- ============================================================
CREATE TABLE IF NOT EXISTS tickets (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    client_id       INT NOT NULL,
    printer_id      INT,
    tech_id         INT,
    status          ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
    priority        ENUM('normal','high','urgent') NOT NULL DEFAULT 'normal',
    type            ENUM('guasto','manutenzione','errore','installazione','consulenza') NOT NULL DEFAULT 'guasto',
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    notes           TEXT,
    travel_time     INT NOT NULL DEFAULT 0,        -- minuti
    work_time       INT NOT NULL DEFAULT 0,        -- minuti
    counter_bw      BIGINT DEFAULT NULL,           -- contatore BN al momento intervento
    counter_color   BIGINT DEFAULT NULL,           -- contatore colore al momento intervento
    resolved        TINYINT(1) NOT NULL DEFAULT 0,
    closed_at       DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL,
    FOREIGN KEY (tech_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- CRONOLOGIA INTERVENTI
-- ============================================================
CREATE TABLE IF NOT EXISTS ticket_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    user_id     INT,
    user_name   VARCHAR(100),
    action      TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- COMPONENTI SOSTITUITI (per intervento)
-- ============================================================
CREATE TABLE IF NOT EXISTS ticket_parts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    part_name   VARCHAR(255) NOT NULL,
    part_code   VARCHAR(100),
    quantity    INT NOT NULL DEFAULT 1,
    notes       VARCHAR(255),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ALLEGATI INTERVENTO
-- ============================================================
CREATE TABLE IF NOT EXISTS ticket_files (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    user_id     INT,
    filename    VARCHAR(255) NOT NULL,
    filepath    VARCHAR(500) NOT NULL,
    filesize    INT,
    mime_type   VARCHAR(100),
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- CONSUMABILI / MAGAZZINO
-- ============================================================
CREATE TABLE IF NOT EXISTS consumables (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200) NOT NULL,
    code        VARCHAR(100) NOT NULL,
    brand       VARCHAR(100),
    type        ENUM('toner','drum','carta','ricambio','altro') NOT NULL DEFAULT 'toner',
    color       VARCHAR(30) DEFAULT '',
    stock       INT NOT NULL DEFAULT 0,
    min_stock   INT NOT NULL DEFAULT 2,
    unit        VARCHAR(20) NOT NULL DEFAULT 'pz',
    price       DECIMAL(10,2) DEFAULT NULL,
    supplier    VARCHAR(150),
    notes       TEXT,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- MOVIMENTI MAGAZZINO
-- ============================================================
CREATE TABLE IF NOT EXISTS stock_movements (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    consumable_id   INT NOT NULL,
    ticket_id       INT DEFAULT NULL,
    user_id         INT DEFAULT NULL,
    type            ENUM('carico','scarico','rettifica') NOT NULL,
    quantity        INT NOT NULL,
    notes           VARCHAR(255),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consumable_id) REFERENCES consumables(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- DATI DI ESEMPIO
-- ============================================================

-- Password = "admin123" (bcrypt) per tutti gli utenti demo
INSERT INTO users (name, email, password, role, avatar, color) VALUES
('Marco Rossi',    'admin@techcopy.it',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',      'MR', '#a78bfa'),
('Elena Martini',  'elena@techcopy.it',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', 'EM', '#e879f9'),
('Luca Ferrari',   'luca@techcopy.it',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tech',       'LF', '#00d4ff'),
('Sara Bianchi',   'sara@techcopy.it',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tech',       'SB', '#39d353'),
('Giorgio Neri',   'giorgio@techcopy.it',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer',     'GN', '#f79000');

INSERT INTO clients (name, contact, phone, email, address, city, zip, lat, lng, notes) VALUES
('Studio Legale Moretti',       'Avv. Paolo Moretti',    '02-4567890', 'moretti@studiolegale.it',  'Via Roma 45',          'Milano',           '20121', 45.4654, 9.1859, 'Ufficio al 3° piano, citofono 12'),
('Farmacia Centrale',           'Dr.ssa Anna Conti',     '02-9876543', 'info@farmaciacentrale.it', 'Corso Buenos Aires 78','Milano',           '20124', 45.4773, 9.2099, 'Aperto 8:00-20:00'),
('Officina Meccanica Belli',    'Sig. Roberto Belli',    '02-3334455', 'belli.officina@gmail.com', 'Via Industria 12',     'Sesto San Giovanni','20099', 45.5329, 9.2325, ''),
('Comune di Rho',               'Sig.ra Teresa Mancini', '02-1122334', 'protocollo@comune.rho.it', 'Piazza Visconti 1',    'Rho',              '20017', 45.5241, 9.0426, 'Accesso dal portone laterale'),
('Istituto Comprensivo Einstein','Preside Luca Vitali',  '02-5544332', 'segreteria@iceinstein.edu.it','Via Galileo 33',    'Cinisello Balsamo','20092', 45.5506, 9.2218, 'Orari scolastici 7:30-17:00');

INSERT INTO printers (client_id, brand, model, serial, type, location, has_adf, has_duplex, has_fax, has_scan, counter_bw, counter_color) VALUES
(1, 'Konica Minolta', 'bizhub C308',        'A7PY000123',  'color', 'Ufficio principale',  1, 1, 1, 1, 45200, 12300),
(1, 'HP',             'LaserJet Pro M428fdn','VNB3S90123',  'bw',    'Sala conferenze',     1, 1, 1, 1, 89000, 0),
(2, 'Ricoh',          'IM C3000',            'E5030012345', 'color', 'Retro cassa',         1, 1, 0, 1, 23400, 8900),
(3, 'Canon',          'imageRUNNER 2630i',   'PPX11234',    'bw',    'Ufficio',             1, 0, 0, 0, 156000, 0),
(4, 'Xerox',          'VersaLink C405',      '3C6A001234',  'color', 'Ufficio tecnico',     1, 1, 1, 1, 34500, 19800),
(5, 'Kyocera',        'TASKalfa 3554ci',     'V2Y390012',   'color', 'Segreteria',          1, 1, 0, 1, 67800, 31200);

INSERT INTO tickets (client_id, printer_id, tech_id, status, priority, type, title, description, travel_time, work_time, counter_bw, counter_color, resolved) VALUES
(1, 1, 2, 'open',   'urgent', 'guasto',       'Carta inceppata frequentemente',         'Il cliente segnala che la carta si inceppa al cassetto 2 ogni 10-15 stampe circa. Errore E04-001.', 35,  0,  45380, 12345, 0),
(2, 3, 3, 'pending','normal', 'manutenzione', 'Manutenzione periodica + cambio toner',  'Scaduta la manutenzione semestrale. Necessario sostituire toner nero e magenta e pulire ADF.',        20,  90, 23480, 8920,  0),
(4, 5, 2, 'closed', 'normal', 'guasto',       'Qualità stampa degradata - righe verticali','Stampe con righe verticali chiare su tutti i colori. Probabile problema al drum o alla testina.', 45,  120,34500, 19780, 1),
(5, 6, 3, 'open',   'high',   'errore',       'Errore di rete - stampante offline',     'La stampante risulta offline dalla rete aziendale dopo aggiornamento firmware.',                       0,   0,  67800, 31200, 0);

INSERT INTO ticket_history (ticket_id, user_id, user_name, action) VALUES
(1, 1, 'Marco Rossi',  'Ticket aperto'),
(1, 2, 'Luca Ferrari', 'Tecnico assegnato, sopralluogo pianificato'),
(2, 1, 'Marco Rossi',  'Ticket aperto'),
(2, 3, 'Sara Bianchi', 'Preso in carico'),
(3, 1, 'Marco Rossi',  'Ticket aperto'),
(3, 2, 'Luca Ferrari', 'Intervento eseguito — risolto'),
(4, 1, 'Marco Rossi',  'Ticket aperto — urgente');

INSERT INTO ticket_parts (ticket_id, part_name, part_code, quantity) VALUES
(2, 'Toner Nero Ricoh IM C3000',    '842312',    1),
(2, 'Toner Magenta Ricoh IM C3000', '842314',    1),
(3, 'Drum Unit Xerox C405 Ciano',   '101R00603', 1),
(3, 'Drum Unit Xerox C405 Magenta', '101R00604', 1);

INSERT INTO consumables (name, code, brand, type, color, stock, min_stock, unit) VALUES
('Toner Nero KM bizhub C308',    'TNP-44K',    'Konica Minolta', 'toner',   'black',   3,  2, 'pz'),
('Toner Ciano KM bizhub C308',   'TNP-44C',    'Konica Minolta', 'toner',   'cyan',    2,  2, 'pz'),
('Toner Magenta KM bizhub C308', 'TNP-44M',    'Konica Minolta', 'toner',   'magenta', 1,  2, 'pz'),
('Toner Giallo KM bizhub C308',  'TNP-44Y',    'Konica Minolta', 'toner',   'yellow',  2,  2, 'pz'),
('Toner Nero Ricoh IM C3000',    '842312',     'Ricoh',          'toner',   'black',   0,  2, 'pz'),
('Toner Magenta Ricoh IM C3000', '842314',     'Ricoh',          'toner',   'magenta', 1,  2, 'pz'),
('Drum Unit Xerox C405 Ciano',   '101R00603',  'Xerox',          'drum',    'cyan',    1,  1, 'pz'),
('Drum Unit Xerox C405 Magenta', '101R00604',  'Xerox',          'drum',    'magenta', 0,  1, 'pz'),
('Carta A4 80g/m² (risma)',      'CARTA-A4-80','Generica',       'carta',   '',        45, 20,'risma'),
('Rullo alimentazione HP LJ Pro','RM1-6414',   'HP',             'ricambio','',        2,  1, 'pz');
