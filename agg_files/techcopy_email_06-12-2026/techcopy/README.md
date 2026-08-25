# TechCopy — Gestione Assistenza Fotocopiatori
## Guida all'installazione su XAMPP

---

## REQUISITI

- XAMPP con **PHP 8.1+** e **MySQL 5.7+ / MariaDB 10.4+**
- Browser moderno (Chrome, Firefox, Edge)
- Apache con mod_rewrite attivo

---

## INSTALLAZIONE PASSO-PASSO

### 1. Copia i file

Copia l'intera cartella `techcopy` nella directory htdocs di XAMPP:

```
C:\xampp\htdocs\techcopy\        (Windows)
/opt/lampp/htdocs/techcopy/      (Linux)
/Applications/XAMPP/htdocs/techcopy/  (macOS)
```

La struttura finale deve essere:
```
htdocs/
└── techcopy/
    ├── index.php
    ├── login.php
    ├── logout.php
    ├── database.sql
    ├── .htaccess
    ├── includes/
    │   ├── config.php      ← configura qui DB
    │   ├── auth.php
    │   ├── helpers.php
    │   └── layout.php
    ├── pages/
    │   ├── tickets.php
    │   ├── ticket_form.php
    │   ├── clients.php
    │   ├── client_form.php
    │   ├── printers.php
    │   ├── printer_form.php
    │   ├── consumables.php
    │   ├── consumable_form.php
    │   ├── users.php
    │   └── map.php
    ├── api/
    │   ├── printers_by_client.php
    │   └── stock.php
    ├── assets/
    │   ├── css/style.css
    │   └── js/app.js
    └── uploads/             ← creata automaticamente, deve essere scrivibile
```

---

### 2. Avvia XAMPP

Apri il **Pannello di Controllo XAMPP** e avvia:
- ✅ **Apache**
- ✅ **MySQL**

---

### 3. Crea il database

**Opzione A — phpMyAdmin (consigliato):**
1. Apri il browser su `http://localhost/phpmyadmin`
2. Clicca su **"Importa"** nel menu in alto
3. Clicca **"Scegli file"** e seleziona `techcopy/database.sql`
4. Clicca **"Esegui"**
5. Il database `techcopy` viene creato con tutte le tabelle e i dati demo.

**Opzione B — Riga di comando:**
```bash
# Windows (XAMPP default path)
C:\xampp\mysql\bin\mysql.exe -u root -p < C:\xampp\htdocs\techcopy\database.sql

# Linux/macOS
mysql -u root -p < /opt/lampp/htdocs/techcopy/database.sql
```

---

### 4. Configura la connessione DB

Apri il file `includes/config.php` e verifica / modifica:

```php
define('DB_HOST',  'localhost');
define('DB_USER',  'root');      // utente MySQL
define('DB_PASS',  '');          // password MySQL (vuota in XAMPP default)
define('DB_NAME',  'techcopy');
```

Se XAMPP usa una password per root, inseriscila nel campo `DB_PASS`.

---

### 5. Permessi cartella uploads (Linux/macOS)

```bash
chmod 755 /opt/lampp/htdocs/techcopy/uploads/
```

Su Windows XAMPP non è necessario.

---

### 6. Accedi all'applicazione

Apri il browser su:
```
http://localhost/techcopy/
```

Verrai reindirizzato alla pagina di login.

---

## ACCOUNT DEMO

Tutti gli account demo hanno password: **`admin123`**

| Nome           | Email                   | Ruolo           |
|----------------|-------------------------|-----------------|
| Marco Rossi    | admin@techcopy.it       | Amministratore  |
| Luca Ferrari   | luca@techcopy.it        | Tecnico         |
| Sara Bianchi   | sara@techcopy.it        | Tecnico         |
| Giorgio Neri   | giorgio@techcopy.it     | Visualizzatore  |

---

## INTEGRAZIONE GOOGLE MAPS (opzionale)

Per attivare la mappa incorporata di Google Maps nelle pagine di dettaglio:

1. Ottieni una chiave API da [console.cloud.google.com](https://console.cloud.google.com)
2. Abilita le API: **Maps JavaScript API** e **Maps Embed API**
3. Aggiungi in `includes/config.php`:
```php
define('GOOGLE_MAPS_KEY', 'LA_TUA_CHIAVE_API_QUI');
```
4. La mappa interattiva apparirà automaticamente nelle pagine cliente e intervento.

---

## STRUTTURA DATABASE

| Tabella            | Descrizione                              |
|--------------------|------------------------------------------|
| `users`            | Utenti del sistema con ruoli             |
| `clients`          | Anagrafica clienti con coordinate GPS    |
| `printers`         | Stampanti/fotocopiatori per cliente      |
| `tickets`          | Interventi/chiamate di assistenza        |
| `ticket_history`   | Cronologia modifiche per ogni intervento |
| `ticket_parts`     | Componenti sostituiti per intervento     |
| `ticket_files`     | Allegati caricati agli interventi        |
| `consumables`      | Magazzino consumabili e ricambi          |
| `stock_movements`  | Movimenti di carico/scarico magazzino    |

---

## PRIVILEGI PER RUOLO

| Funzione                        | Admin | Tecnico | Viewer |
|---------------------------------|:-----:|:-------:|:------:|
| Vedere dashboard e lista        | ✅    | ✅      | ✅     |
| Aprire nuovo intervento         | ✅    | ✅      | ❌     |
| Modificare intervento assegnato | ✅    | ✅*     | ❌     |
| Modificare qualsiasi intervento | ✅    | ❌      | ❌     |
| Gestire clienti                 | ✅    | ❌      | ❌     |
| Gestire stampanti               | ✅    | ❌      | ❌     |
| Gestire consumabili / stock     | ✅    | ❌      | ❌     |
| Gestire utenti                  | ✅    | ❌      | ❌     |

*I tecnici possono modificare solo gli interventi a loro assegnati.

---

## RISOLUZIONE PROBLEMI

**Pagina bianca / errore PHP:**
- Controlla che PHP 8.1+ sia attivo in XAMPP
- Verifica i log in `C:\xampp\apache\logs\error.log`

**Errore connessione database:**
- Assicurati che MySQL sia avviato in XAMPP
- Controlla le credenziali in `includes/config.php`
- Verifica che il database `techcopy` esista in phpMyAdmin

**Upload file non funziona:**
- Controlla che la cartella `uploads/` esista e sia scrivibile
- Verifica il limite `upload_max_filesize` in `php.ini` (XAMPP default: 2MB, impostare a 10M)

**mod_rewrite non funziona (Windows):**
- In `C:\xampp\apache\conf\httpd.conf` assicurati che sia presente:
  `LoadModule rewrite_module modules/mod_rewrite.so`
- Imposta `AllowOverride All` per la directory htdocs

---

## PERSONALIZZAZIONE

- **Logo/nome azienda**: modifica `APP_NAME` in `includes/config.php` e il testo in `includes/layout.php`
- **Colori tema**: modifica le variabili CSS in `assets/css/style.css` (sezione `:root`)
- **Nuovi ruoli**: aggiungi valori ENUM alla colonna `role` in `users` e aggiorna la logica in `includes/auth.php`

---

*TechCopy v1.0 — Sistema di gestione assistenza fotocopiatori*
