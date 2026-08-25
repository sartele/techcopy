<?php
// ============================================================
//  TechCopy — Script di setup / reset password
//  
//  COME USARLO:
//  1. Copia questo file nella cartella  C:\xampp\htdocs\techcopy\
//  2. Apri il browser su  http://localhost/techcopy/setup.php
//  3. Clicca "Esegui Setup"
//  4. Dopo il successo ELIMINA questo file per sicurezza
// ============================================================

// Configurazione DB (stessa di includes/config.php)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          // cambia se hai una password MySQL
$DB_NAME = 'techcopy';

$messages = [];
$errors   = [];
$done     = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO(
            "mysql:host={$DB_HOST};charset=utf8mb4",
            $DB_USER, $DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 1. Crea database se non esiste
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$DB_NAME}`");
        $messages[] = "✅ Database '{$DB_NAME}' pronto.";

        // 2. Crea tabelle
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(100) NOT NULL,
            email      VARCHAR(150) NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            role       ENUM('admin','supervisor','tech','viewer') NOT NULL DEFAULT 'tech',
            avatar     VARCHAR(4) NOT NULL DEFAULT 'US',
            color      VARCHAR(10) NOT NULL DEFAULT '#00d4ff',
            active     TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(150) NOT NULL,
            contact    VARCHAR(100),
            phone      VARCHAR(30),
            email      VARCHAR(150),
            address    VARCHAR(255),
            city       VARCHAR(100),
            zip        VARCHAR(10),
            lat        DECIMAL(10,7) DEFAULT NULL,
            lng        DECIMAL(10,7) DEFAULT NULL,
            notes      TEXT,
            active     TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS printers (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            client_id     INT NOT NULL,
            brand         VARCHAR(80) NOT NULL,
            model         VARCHAR(100) NOT NULL,
            serial        VARCHAR(80) NOT NULL,
            type          ENUM('color','bw') NOT NULL DEFAULT 'bw',
            location      VARCHAR(150),
            has_adf       TINYINT(1) NOT NULL DEFAULT 0,
            has_duplex    TINYINT(1) NOT NULL DEFAULT 0,
            has_fax       TINYINT(1) NOT NULL DEFAULT 0,
            has_scan      TINYINT(1) NOT NULL DEFAULT 0,
            counter_bw    BIGINT NOT NULL DEFAULT 0,
            counter_color BIGINT NOT NULL DEFAULT 0,
            purchase_date DATE DEFAULT NULL,
            warranty_exp  DATE DEFAULT NULL,
            notes         TEXT,
            active        TINYINT(1) NOT NULL DEFAULT 1,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            client_id     INT NOT NULL,
            printer_id    INT,
            tech_id       INT,
            status        ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
            priority      ENUM('normal','high','urgent') NOT NULL DEFAULT 'normal',
            type          ENUM('guasto','manutenzione','errore','installazione','consulenza') NOT NULL DEFAULT 'guasto',
            title         VARCHAR(255) NOT NULL,
            description   TEXT,
            notes         TEXT,
            travel_time   INT NOT NULL DEFAULT 0,
            work_time     INT NOT NULL DEFAULT 0,
            counter_bw    BIGINT DEFAULT NULL,
            counter_color BIGINT DEFAULT NULL,
            resolved      TINYINT(1) NOT NULL DEFAULT 0,
            closed_at     DATETIME DEFAULT NULL,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
            FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL,
            FOREIGN KEY (tech_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_history (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id  INT NOT NULL,
            user_id    INT,
            user_name  VARCHAR(100),
            action     TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_parts (
            id        INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            part_name VARCHAR(255) NOT NULL,
            part_code VARCHAR(100),
            quantity  INT NOT NULL DEFAULT 1,
            notes     VARCHAR(255),
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_files (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id  INT NOT NULL,
            user_id    INT,
            filename   VARCHAR(255) NOT NULL,
            filepath   VARCHAR(500) NOT NULL,
            filesize   INT,
            mime_type  VARCHAR(100),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS consumables (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(200) NOT NULL,
            code       VARCHAR(100) NOT NULL,
            brand      VARCHAR(100),
            type       ENUM('toner','drum','carta','ricambio','altro') NOT NULL DEFAULT 'toner',
            color      VARCHAR(30) DEFAULT '',
            stock      INT NOT NULL DEFAULT 0,
            min_stock  INT NOT NULL DEFAULT 2,
            unit       VARCHAR(20) NOT NULL DEFAULT 'pz',
            price      DECIMAL(10,2) DEFAULT NULL,
            supplier   VARCHAR(150),
            notes      TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            consumable_id  INT NOT NULL,
            ticket_id      INT DEFAULT NULL,
            user_id        INT DEFAULT NULL,
            type           ENUM('carico','scarico','rettifica') NOT NULL,
            quantity       INT NOT NULL,
            notes          VARCHAR(255),
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (consumable_id) REFERENCES consumables(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $messages[] = "✅ Tutte le tabelle create / verificate.";

        // 3. Genera hash corretti per "admin123"
        $pwd = password_hash('admin123', PASSWORD_DEFAULT);

        // 4. Inserisci utenti demo (ignora se esistono già)
        $users = [
            ['Marco Rossi',   'admin@techcopy.it',   'admin',      'MR', '#a78bfa'],
            ['Elena Martini', 'elena@techcopy.it',   'supervisor', 'EM', '#e879f9'],
            ['Luca Ferrari',  'luca@techcopy.it',    'tech',       'LF', '#00d4ff'],
            ['Sara Bianchi',  'sara@techcopy.it',    'tech',       'SB', '#39d353'],
            ['Giorgio Neri',  'giorgio@techcopy.it', 'viewer',     'GN', '#f79000'],
        ];
        $ins = $pdo->prepare("INSERT IGNORE INTO users (name,email,password,role,avatar,color) VALUES (?,?,?,?,?,?)");
        $upd = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
        foreach ($users as $u) {
            $ins->execute([$u[0], $u[1], $pwd, $u[2], $u[3], $u[4]]);
            $upd->execute([$pwd, $u[1]]); // aggiorna anche se già esisteva
        }
        $messages[] = "✅ Utenti demo inseriti/aggiornati (password: <strong>admin123</strong>).";

        // 5. Inserisci clienti demo
        $existingClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
        if ($existingClients == 0) {
            $ic = $pdo->prepare("INSERT INTO clients (name,contact,phone,email,address,city,zip,lat,lng,notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $clientsData = [
                ['Studio Legale Moretti','Avv. Paolo Moretti','02-4567890','moretti@studiolegale.it','Via Roma 45','Milano','20121',45.4654,9.1859,'Ufficio al 3° piano, citofono 12'],
                ['Farmacia Centrale','Dr.ssa Anna Conti','02-9876543','info@farmaciacentrale.it','Corso Buenos Aires 78','Milano','20124',45.4773,9.2099,'Aperto 8:00-20:00'],
                ['Officina Meccanica Belli','Sig. Roberto Belli','02-3334455','belli.officina@gmail.com','Via Industria 12','Sesto San Giovanni','20099',45.5329,9.2325,''],
                ['Comune di Rho','Sig.ra Teresa Mancini','02-1122334','protocollo@comune.rho.it','Piazza Visconti 1','Rho','20017',45.5241,9.0426,'Accesso dal portone laterale'],
                ['Istituto Comprensivo Einstein','Preside Luca Vitali','02-5544332','segreteria@iceinstein.edu.it','Via Galileo 33','Cinisello Balsamo','20092',45.5506,9.2218,'Orari scolastici 7:30-17:00'],
            ];
            foreach ($clientsData as $c) $ic->execute($c);
            $messages[] = "✅ Clienti demo inseriti.";
        } else {
            $messages[] = "ℹ️ Clienti già presenti, saltati.";
        }

        // 6. Inserisci stampanti demo
        $existingPrinters = $pdo->query("SELECT COUNT(*) FROM printers")->fetchColumn();
        if ($existingPrinters == 0) {
            $ip = $pdo->prepare("INSERT INTO printers (client_id,brand,model,serial,type,location,has_adf,has_duplex,has_fax,has_scan,counter_bw,counter_color) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $printersData = [
                [1,'Konica Minolta','bizhub C308','A7PY000123','color','Ufficio principale',1,1,1,1,45200,12300],
                [1,'HP','LaserJet Pro M428fdn','VNB3S90123','bw','Sala conferenze',1,1,1,1,89000,0],
                [2,'Ricoh','IM C3000','E5030012345','color','Retro cassa',1,1,0,1,23400,8900],
                [3,'Canon','imageRUNNER 2630i','PPX11234','bw','Ufficio',1,0,0,0,156000,0],
                [4,'Xerox','VersaLink C405','3C6A001234','color','Ufficio tecnico',1,1,1,1,34500,19800],
                [5,'Kyocera','TASKalfa 3554ci','V2Y390012','color','Segreteria',1,1,0,1,67800,31200],
            ];
            foreach ($printersData as $p) $ip->execute($p);
            $messages[] = "✅ Stampanti demo inserite.";
        } else {
            $messages[] = "ℹ️ Stampanti già presenti, saltate.";
        }

        // 7. Inserisci ticket demo
        $existingTickets = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
        if ($existingTickets == 0) {
            $techId2 = $pdo->query("SELECT id FROM users WHERE email='luca@techcopy.it'")->fetchColumn();
            $techId3 = $pdo->query("SELECT id FROM users WHERE email='sara@techcopy.it'")->fetchColumn();
            $it = $pdo->prepare("INSERT INTO tickets (client_id,printer_id,tech_id,status,priority,type,title,description,travel_time,work_time,counter_bw,counter_color,resolved) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ticketsData = [
                [1,1,$techId2,'open','urgent','guasto','Carta inceppata frequentemente','Il cliente segnala che la carta si inceppa al cassetto 2 ogni 10-15 stampe circa. Errore E04-001.',35,0,45380,12345,0],
                [2,3,$techId3,'pending','normal','manutenzione','Manutenzione periodica + cambio toner','Scaduta la manutenzione semestrale. Necessario sostituire toner nero e magenta e pulire ADF.',20,90,23480,8920,0],
                [4,5,$techId2,'closed','normal','guasto','Qualità stampa degradata - righe verticali','Stampe con righe verticali chiare su tutti i colori. Probabile problema al drum o alla testina.',45,120,34500,19780,1],
                [5,6,$techId3,'open','high','errore','Errore di rete - stampante offline','La stampante risulta offline dalla rete aziendale dopo aggiornamento firmware.',0,0,67800,31200,0],
            ];
            foreach ($ticketsData as $t) {
                $it->execute($t);
                $tid = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO ticket_history (ticket_id,user_id,user_name,action) VALUES (?,1,'Marco Rossi','Ticket aperto')")->execute([$tid]);
            }
            // Parti sostituite ticket 2 e 3
            $tid2 = $pdo->query("SELECT id FROM tickets LIMIT 1 OFFSET 1")->fetchColumn();
            $tid3 = $pdo->query("SELECT id FROM tickets LIMIT 1 OFFSET 2")->fetchColumn();
            $ipart = $pdo->prepare("INSERT INTO ticket_parts (ticket_id,part_name,part_code,quantity) VALUES (?,?,?,1)");
            $ipart->execute([$tid2,'Toner Nero Ricoh IM C3000','842312']);
            $ipart->execute([$tid2,'Toner Magenta Ricoh IM C3000','842314']);
            $ipart->execute([$tid3,'Drum Unit Xerox C405 Ciano','101R00603']);
            $ipart->execute([$tid3,'Drum Unit Xerox C405 Magenta','101R00604']);
            $messages[] = "✅ Interventi demo inseriti.";
        } else {
            $messages[] = "ℹ️ Interventi già presenti, saltati.";
        }

        // 8. Inserisci consumabili
        $existingCons = $pdo->query("SELECT COUNT(*) FROM consumables")->fetchColumn();
        if ($existingCons == 0) {
            $icon = $pdo->prepare("INSERT INTO consumables (name,code,brand,type,color,stock,min_stock,unit) VALUES (?,?,?,?,?,?,?,?)");
            $consData = [
                ['Toner Nero KM bizhub C308','TNP-44K','Konica Minolta','toner','black',3,2,'pz'],
                ['Toner Ciano KM bizhub C308','TNP-44C','Konica Minolta','toner','cyan',2,2,'pz'],
                ['Toner Magenta KM bizhub C308','TNP-44M','Konica Minolta','toner','magenta',1,2,'pz'],
                ['Toner Giallo KM bizhub C308','TNP-44Y','Konica Minolta','toner','yellow',2,2,'pz'],
                ['Toner Nero Ricoh IM C3000','842312','Ricoh','toner','black',0,2,'pz'],
                ['Toner Magenta Ricoh IM C3000','842314','Ricoh','toner','magenta',1,2,'pz'],
                ['Drum Unit Xerox C405 Ciano','101R00603','Xerox','drum','cyan',1,1,'pz'],
                ['Drum Unit Xerox C405 Magenta','101R00604','Xerox','drum','magenta',0,1,'pz'],
                ['Carta A4 80g/m² (risma)','CARTA-A4-80','Generica','carta','',45,20,'risma'],
                ['Rullo alimentazione HP LJ Pro','RM1-6414','HP','ricambio','',2,1,'pz'],
            ];
            foreach ($consData as $c) $icon->execute($c);
            $messages[] = "✅ Consumabili demo inseriti.";
        } else {
            $messages[] = "ℹ️ Consumabili già presenti, saltati.";
        }

        $done = true;

    } catch (PDOException $e) {
        $errors[] = "❌ Errore database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TechCopy — Setup</title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',sans-serif; background:#0d1117; color:#e6edf3; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
  .box { width:560px; background:#161b22; border:1px solid #2a3448; border-radius:12px; padding:40px; box-shadow:0 8px 40px rgba(0,0,0,.5); }
  h1 { font-size:22px; color:#00d4ff; margin-bottom:6px; font-family:monospace; }
  .sub { font-size:13px; color:#8b98a8; margin-bottom:28px; }
  .step { background:#1c2230; border:1px solid #2a3448; border-radius:8px; padding:16px; margin-bottom:12px; font-size:13px; }
  .step code { background:#0d1117; padding:2px 6px; border-radius:4px; font-family:monospace; font-size:12px; color:#00d4ff; }
  .btn { display:block; width:100%; padding:14px; background:#00d4ff; color:#000; border:none; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; margin-top:20px; letter-spacing:.3px; }
  .btn:hover { background:#00b8e0; }
  .msg { padding:10px 14px; border-radius:6px; font-size:13px; margin-bottom:8px; }
  .msg-ok  { background:rgba(57,211,83,.1); border:1px solid #39d353; color:#39d353; }
  .msg-err { background:rgba(244,71,71,.1); border:1px solid #f44747; color:#f44747; }
  .success-box { text-align:center; padding:24px; background:rgba(57,211,83,.08); border:1px solid #39d353; border-radius:10px; margin-top:20px; }
  .success-box h2 { color:#39d353; font-size:20px; margin-bottom:10px; }
  .success-box p { font-size:13px; color:#8b98a8; line-height:1.6; }
  .creds { background:#0d1117; border:1px solid #2a3448; border-radius:8px; padding:16px; margin:16px 0; text-align:left; }
  .creds table { width:100%; border-collapse:collapse; font-size:12px; }
  .creds td { padding:6px 8px; border-bottom:1px solid #2a3448; }
  .creds td:first-child { color:#8b98a8; font-family:monospace; }
  .creds tr:last-child td { border-bottom:none; }
  .warning { background:rgba(247,144,0,.1); border:1px solid #f79000; border-radius:6px; padding:12px 14px; font-size:12px; color:#f79000; margin-top:16px; }
  a.go { display:inline-block; margin-top:16px; padding:12px 28px; background:#00d4ff; color:#000; border-radius:8px; font-weight:700; font-size:14px; text-decoration:none; }
  a.go:hover { background:#00b8e0; }
</style>
</head>
<body>
<div class="box">
  <h1>🖨️ TechCopy — Setup iniziale</h1>
  <p class="sub">Questo script crea il database, tutte le tabelle e i dati demo con le password corrette.</p>

  <?php if (!$done): ?>

  <div class="step">
    <strong>Configurazione rilevata:</strong><br>
    Host: <code><?= htmlspecialchars($DB_HOST) ?></code> &nbsp;
    DB: <code><?= htmlspecialchars($DB_NAME) ?></code> &nbsp;
    Utente: <code><?= htmlspecialchars($DB_USER) ?></code>
    <?php if (!empty($errors)): ?>
      <br><br>
      <?php foreach ($errors as $e): ?>
        <div class="msg msg-err"><?= $e ?></div>
      <?php endforeach; ?>
      <p style="margin-top:8px;font-size:12px;color:#8b98a8">
        Se la password MySQL è sbagliata, aprire <code>setup.php</code> e modificare <code>$DB_PASS</code> in cima al file.
      </p>
    <?php endif; ?>
  </div>

  <div class="step">
    <strong>Cosa verrà fatto:</strong><br><br>
    • Crea database <code>techcopy</code> se non esiste<br>
    • Crea 9 tabelle (users, clients, printers, tickets, ...)<br>
    • Inserisce 4 utenti demo con password <code>admin123</code><br>
    • Inserisce clienti, stampanti, interventi e consumabili demo
  </div>

  <form method="POST">
    <button type="submit" class="btn">▶ Esegui Setup</button>
  </form>

  <?php else: ?>

  <?php foreach ($messages as $m): ?>
    <div class="msg msg-ok"><?= $m ?></div>
  <?php endforeach; ?>

  <div class="success-box">
    <h2>✅ Setup completato!</h2>
    <p>Il database è stato configurato correttamente.<br>Puoi accedere con questi account:</p>
    <div class="creds">
      <table>
        <tr><td>admin@techcopy.it</td><td>admin123</td><td><span style="color:#a78bfa">Amministratore</span></td></tr>
        <tr><td>elena@techcopy.it</td><td>admin123</td><td><span style="color:#e879f9">Supervisore</span></td></tr>
        <tr><td>luca@techcopy.it</td><td>admin123</td><td><span style="color:#00d4ff">Tecnico</span></td></tr>
        <tr><td>sara@techcopy.it</td><td>admin123</td><td><span style="color:#00d4ff">Tecnico</span></td></tr>
        <tr><td>giorgio@techcopy.it</td><td>admin123</td><td><span style="color:#f79000">Visualizzatore</span></td></tr>
      </table>
    </div>
    <a href="/techcopy/login.php" class="go">→ Vai al Login</a>
  </div>

  <div class="warning">
    ⚠️ <strong>Importante:</strong> Elimina il file <code>setup.php</code> dalla cartella
    <code>htdocs/techcopy/</code> dopo aver verificato l'accesso, per ragioni di sicurezza.
  </div>

  <?php endif; ?>
</div>
</body>
</html>
