# TechCopy v2.0 — Gestione Assistenza Stampanti & Fotocopiatori

## Installazione su XAMPP (3 passi)

### 1. Copia i file
Estrai lo zip e copia la cartella `techcopy/` in:
```
C:\xampp\htdocs\techcopy\        (Windows)
/opt/lampp/htdocs/techcopy/      (Linux)
```

### 2. Esegui il setup
Avvia Apache e MySQL in XAMPP, poi apri:
```
http://localhost/techcopy/setup.php
```
Clicca **"Esegui Setup"** — crea il database, le tabelle e gli utenti demo.
**Elimina `setup.php` dopo il primo accesso.**

### 3. Accedi
```
http://localhost/techcopy/
```

---

## Account demo (password: `admin123`)

| Email | Ruolo | Permessi |
|---|---|---|
| admin@techcopy.it | 👑 Amministratore | Tutto, compresa gestione utenti ed eliminazione |
| chiara@techcopy.it | 🔍 Supervisore | Vede tutti gli interventi, gestisce clienti/stampanti/consumabili |
| luca@techcopy.it | 🔧 Tecnico | Solo i propri interventi, apre nuovi ticket, vede mappa |
| sara@techcopy.it | 🔧 Tecnico | Idem |
| giorgio@techcopy.it | 👁 Visualizzatore | Solo lettura su tutto |

---

## Struttura file

```
techcopy/
├── setup.php            ← Eseguire SOLO la prima volta, poi eliminare
├── login.php
├── logout.php
├── index.php            ← Dashboard
├── database.sql         ← Schema per import manuale in phpMyAdmin
├── .htaccess
├── includes/
│   ├── config.php       ← Configura DB_HOST / DB_USER / DB_PASS
│   ├── auth.php         ← Autenticazione e controllo ruoli
│   ├── helpers.php      ← Funzioni di utilità
│   └── layout.php       ← Sidebar, topbar, footer HTML
├── pages/
│   ├── tickets.php      ← Gestione interventi (CRUD completo)
│   ├── ticket_form.php  ← Nuovo intervento
│   ├── clients.php      ← Anagrafica clienti
│   ├── client_form.php  ← Nuovo cliente
│   ├── printers.php     ← Stampanti/fotocopiatori
│   ├── printer_form.php ← Nuova stampante
│   ├── consumables.php  ← Magazzino consumabili
│   ├── consumable_form.php
│   ├── users.php        ← Gestione utenti (solo admin)
│   └── map.php          ← Mappa clienti OpenStreetMap
├── api/
│   ├── printers_by_client.php  ← AJAX: stampanti per cliente
│   └── stock.php               ← AJAX: aggiornamento stock
├── assets/
│   ├── css/style.css
│   └── js/app.js
└── uploads/             ← Allegati interventi (scrivibile)
```

---

## Tabella database `printers` — campi principali

| Campo | Tipo | Descrizione |
|---|---|---|
| brand, model, serial | VARCHAR | Marca, modello, numero di serie |
| type | ENUM(color, bw) | Colore o monocromatico |
| rete_lan | TINYINT | Connessione LAN cablata |
| rete_wifi | TINYINT | Connessione WiFi |
| adf | ENUM(none, simple, duplex) | Nessun ADF / Solo fronte / Fronte-retro |
| has_duplex | TINYINT | Stampa fronte-retro automatica |
| has_scan | TINYINT | Scanner di rete |
| counter_bw | BIGINT | Contatore copie B/N |
| counter_color | BIGINT | Contatore copie colore |

---

## Gerarchia ruoli

| Funzione | Admin | Supervisor | Tecnico | Viewer |
|---|:---:|:---:|:---:|:---:|
| Gestione utenti | ✅ | ❌ | ❌ | ❌ |
| Elimina ticket/stampante | ✅ | ❌ | ❌ | ❌ |
| Vede tutti gli interventi | ✅ | ✅ | ❌ (solo suoi) | ✅ |
| Inserisce/modifica clienti | ✅ | ✅ | ❌ | ❌ |
| Inserisce/modifica stampanti | ✅ | ✅ | ❌ | ❌ |
| Apre nuovo intervento | ✅ | ✅ | ✅ | ❌ |
| Modifica intervento assegnato | ✅ | ✅ | ✅ | ❌ |
| Gestisce consumabili/stock | ✅ | ✅ | ❌ | ❌ |
| Mappa clienti | ✅ | ✅ | ✅ | ✅ |
| Vede dashboard/clienti/stampanti | ✅ | ✅ | ✅ | ✅ |

---

## Configurazione (includes/config.php)

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // password MySQL XAMPP
define('DB_NAME', 'techcopy');
define('UPLOAD_MAX_MB', 10);  // dimensione massima allegati
```

---

## Mappa clienti
La mappa usa **OpenStreetMap + Leaflet** — completamente gratuita, nessuna chiave API.
Il geocoding automatico usa Nominatim (OSM): se un cliente ha l'indirizzo ma non le coordinate GPS, vengono recuperate automaticamente al primo caricamento della mappa.

I pulsanti di navigazione nel dettaglio intervento aprono:
- 🗺️ OpenStreetMap
- 🔍 Google Maps
- 🚗 Waze

---

*TechCopy v2.0 — Ricostruito da zero con tutte le correzioni*
