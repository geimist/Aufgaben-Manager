# 🎯 Aufgaben Manager

> Eine moderne, browser-basierte Task-Management-Anwendung zur effizienten Organisation von Aufgaben und Terminen mit optionaler Cloud-Synchronisation.

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/geimist/Aufgaben-Manager)
[![License](https://img.shields.io/badge/license-GPL--3.0-blue.svg)](#Lizenz)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow.svg)](https://developer.mozilla.org/de/docs/Web/JavaScript)

## 📋 Inhaltsverzeichnis

- [🎯 Aufgaben Manager](#-aufgaben-manager)
- [🌟 Grundfunktion](#-grundfunktion)
- [✨ Features](#-features)
  - [Ereignis-Verwaltung](#ereignis-verwaltung)
  - [Aufgaben-Planung](#aufgaben-planung)
  - [Intelligente To-Do-Liste](#intelligente-to-do-liste)
  - [Kalender-Export](#kalender-export)
  - [Cloud-Synchronisation](#cloud-synchronisation)
- [🚀 Setup](#-setup)
  - [Lokale Nutzung](#lokale-nutzung)
  - [Server-Setup](#server-setup)
- [📖 Verwendung](#-verwendung)
- [🔧 Sicherheit](#-sicherheit)
- [🆘 Support](#-support)

---

## 🌟 Grundfunktion

Der **Aufgaben Manager** ist eine intuitive Webanwendung für die Organisation von Aufgaben und Terminen. Die Kernidee basiert auf dem Konzept, für jeden wichtigen Termin automatisch die erforderlichen Vorbereitungsaufgaben zu planen und zu verfolgen.

**Hauptzweck:** Automatische Generierung von To-Do-Listen aus Events und deren zugehörigen Aufgaben mit intelligenten Deadlines und Dringlichkeitskennzeichnungen.

---

## ✨ Features

### Ereignis-Verwaltung
- 📅 **Ereignisse hinzufügen** mit Datum und Beschreibung
- ✏️ **Ereignisse bearbeiten** und duplizieren
- 📋 **Übersichtliche Tabellenanzeige** aller Termine
- 🔍 **Automatische Vervollständigung** bei Eingabe

### Aufgaben-Planung
- 🎯 **Aufgaben definieren** für bestimmte Ereignistypen
- ⏰ **Flexible Zeitplanung**:
  - *Davor* (z.B. 14 Tage vor dem Event)
  - *Am* (am Tag des Events)
  - *Danach* (z.B. 1 Woche nach dem Event)
- 🔄 **Wiederkehrende Aufgaben** mit konfigurierbaren Intervallen
- 📝 **Beschreibungen und Quellen** (URLs, Texte)
- 🔗 **Quell-Links** als anklickbare Links

### Intelligente To-Do-Liste
- 🎯 **Automatische To-Do-Generierung** aus Events und Tasks
- 📊 **Status-Übersicht** mit Filteroptionen:
  - ⚪️ **Erledigt** (abgeschlossene Aufgaben)
  - 🟠 **Anstehend** (kritische/hohe Dringlichkeit)
  - 🟢 **Offen** (normale Aufgaben)
  - 🔴 **Überfällig** (überfällige Deadlines)
- 🎨 **Farbkodierung** nach Dringlichkeit
- 📱 **Responsive Design** für alle Geräte

### Kalender-Export
- 📅 **ICS-Datei-Export** für alle Aufgaben
- 📧 **Kompatibilität** mit Outlook, Google Calendar, Apple Calendar
- ⏰ **Automatische Termin-Erstellung** im Kalender
- 📝 **Vollständige Termin-Details** mit Beschreibungen

### Cloud-Synchronisation *(optional)*
- ☁️ **Server-Synchronisation** mit SQLite-Datenbank
- 🔄 **Automatische Synchronisation** bei Änderungen
- 💾 **Backup-Versionen** mit Wiederherstellungsoptionen
- 🏷️ **Geräte-Verwaltung** mit benutzerdefinierten Namen
- 📞 **Kontakt-Formular** für Support-Anfragen
- ⚡ **Konflikt-Lösung** bei parallelen Änderungen

---

## 🚀 Setup

Die Anwendung kann sowohl **lokal** als auch mit **Server-Synchronisation** genutzt werden. Die Wahl hängt von deinen Bedürfnissen ab:

### Lokale Nutzung *(empfohlen für Einzelplatznutzer)*

1. **Download der Anwendung:**
   ```bash
   # HTML-Dateien in beliebigem Verzeichnis speichern
   Aufgaben-Manager.html          # Hauptversion
   ```

2. **Browser öffnen:**
   - Doppelklick auf `Aufgaben-Manager.html`
   - Oder über Browser-Menü öffnen

3. **Datenspeicherung:**
   - Alle Daten werden im Browser localStorage gespeichert
   - SQLite-Datenbank läuft clientseitig mit WebAssembly

**Vorteile:**
- ✅ **Datenschutz:** Keine Datenübertragung
- ✅ **Offline-Fähigkeit:** Vollständige Funktionalität ohne Internet
- ✅ **Einfachheit:** Keine Server-Konfiguration nötig

### Server-Setup *(für Multi-Geräte-Synchronisation)*

Die Server-Komponente ermöglicht die Synchronisation zwischen mehreren Geräten und bietet zusätzliche Funktionen wie Backup-Versionen und ein Kontakt-Formular.

## Sicherheit
- **UID-basierte Zugangskontrolle**: Jeder Benutzer braucht eine eindeutige UID
- **Keine Passwörter**: Einfache Sicherheit durch URL und UID
- **optionale Verschlüsselung**: Im Dialog für die Serverkonfiguration kann ein Synchronisationsschlüssel hinterlegt werden

## Server-Setup

### 1. Voraussetzungen
- PHP 5.6 oder höher
- Webserver (Apache, Nginx oder ähnlich) mit PHP-Unterstützung
- Schreibrechte für den `sync_data/`-Ordner

### 2. Dateien hochladen
Laden Sie folgende Dateien auf Ihren Webserver:
```
sync.php
```

**Wichtig:** Der Webserver muss diese Dateien unter einer öffentlich zugänglichen URL erreichbar machen.

### 3. Ordner-Berechtigung
Stellen Sie sicher, dass der Webserver Schreibrechte für den Ordner hat:
```bash
# Beispiel für Linux/Apache:
sudo mkdir -p /var/www/html/sync_data
sudo chown www-data:www-data /var/www/html/sync_data
sudo chmod 755 /var/www/html/sync_data
```

### 4. Test der Installation
Testen Sie die Installation mit einem der folgenden Befehle:

**cURL-Test (empfohlen):**
```bash
curl -X POST https://IhreDomain.com/sync.php \
  -H "Content-Type: application/json" \
  -d '{"action":"test","uid":"test123"}'
```

**Erwartete Antwort:**
```json
{
    "success": false,
    "message": "Ungültige Aktion"
}
```

**Alternativer Browser-Test:**
```bash
# Im Browser die Developer-Konsole öffnen und ausführen:
fetch('https://IhreDomain.com/sync.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({action: 'test', uid: 'test123'})
}).then(r => r.text()).then(console.log);
```

Wenn die Installation korrekt ist, erhalten Sie eine JSON-Antwort anstelle eines 404-Fehlers.

## Client-Konfiguration

### 1. Server-URL konfigurieren
1. Öffne die ToDo Manager-App
2. Klicke auf das Zahnrad-Symbol (⚙️) in der oberen rechten Ecke
3. Wähle "Server-Einstellungen"
4. Trage ein:
   - **Server-URL**: `https://IhreDomain.com/sync.php`
   - **Benutzer-ID (UID)**: Eine eindeutige ID (z.B. `benutzer123`)

### 2. UID vergeben
- Die UID dient als einfache Zugangskontrolle
- Sie sollte eindeutig sein und nur aus Buchstaben, Zahlen, Unterstrichen und Bindestrichen bestehen
- Empfehlung: Verwende eine Kombination aus Ihrem Namen und einer Zahl

## Verwendung

### Daten hochladen (Upload)
1. Stelle sicher, dass die Server-Konfiguration korrekt eingestellt ist
2. Klicke im Zahnrad-Menü auf "Upload zur Datenbank"
3. Die Daten werden zum Server übertragen

### Daten herunterladen (Download)
1. Stelle sicher, dass die Server-Konfiguration korrekt eingestellt ist
2. Klicke im Zahnrad-Menü auf "Download vom Server"
3. **Wichtig:** Lokale Änderungen könnten überschrieben werden!

## Datenstruktur auf dem Server

### Ordner-Struktur
```
/sync_data/
  └── [UID]/
      ├── index.json          # Nutzer-Informationen
      ├── current.json        # Aktuellste Datenbank
      ├── db_YYYYMMDD_HHMMSS_XXXXXXXXXXXXXXXX.json  # Zeitstempelte Backups
```

### Index-Datei (index.json)
```json
{
    "uid": "benutzer123",
    "created": "2025-09-02 22:00:00",
    "last_sync": "2025-09-02 22:15:30",
    "version": "1.0"
}
```

### Backup-System
- **Automatische Backups**: Bei jedem Upload wird eine neue Datei erstellt
- **Maximale Backups**: Standardmäßig 50 Backups pro Benutzer
- **Cleanup**: Ältere Backups werden automatisch gelöscht

## Fehlerbehebung

### HTTP-Fehler (400/500)
- Überprüfe die Server-URL
- Stelle sicher, dass die PHP-Datei korrekt hochgeladen wurde
- Kontrolliere die Server-Logs

### "Keine Daten gefunden"
- Überprüfe die UID in der Konfiguration
- Stelle sicher, dass bereits Daten hochgeladen wurden
- Kontrolliere die Schreibrechte

### Konflikte bei parallelen Änderungen
- **Upload überschreibt Server-Daten** ohne Nachfrage
- **Download fordert Bestätigung** bevor lokale Daten überschrieben werden
- Empfehlung: Regelmäßig synchronisieren und große Änderungen nicht parallel machen

## 📖 Verwendung

### Erste Schritte

1. **Anwendung öffnen**
   - Öffne `Aufgaben-Manager.html` in deinem Browser
   - Die Anwendung lädt automatisch und initialisiert die Datenbank

2. **Ereignisse hinzufügen**
   - Klicke auf "Ereignis hinzufügen"
   - Gib einen Namen, Datum und eine optionale Beschreibung ein
   - Verwendung der automatischen Vervollständigung für wiederkehrende Event-Typen

3. **Aufgaben definieren**
   - Wechsle zur "Tasks"-Registerkarte
   - Erstelle Aufgaben für bestimmte Ereignistypen
   - Lege Timing, Beschreibungen und Quellen fest

4. **To-Dos überwachen**
   - Wechsle zur "To-Dos für Events"-Registerkarte
   - Verwende Filter um den Überblick zu behalten
   - Markiere erledigte Aufgaben ab

### Beispiel-Workflow

```
🏃‍♂️ Urlaub planen → 14 Tage vorher
├── Flug buchen → 21 Tage vorher
├── Hotel reservieren → 14 Tage vorher
├── Reiseroute planen → 7 Tage vorher
└── Koffer packen → 2 Tage vorher
```

---

## 🛠️ Erweiterte Konfiguration

### Dringlichkeit anpassen

In der Anwendung kannst du die Dringlichkeits-Schwellenwerte ändern:

```javascript
const DEADLINE_CONFIG = {
    critical: 7,    // Kritischer Zeitraum (< 7 Tage)
    warning: 14,    // Warning-Bereich (8-14 Tage)
    soon: 31        // Soon-Bereich (15-31 Tage)
};
```

### Design-Anpassungen

Die Farben und das Erscheinungsbild können über CSS-Variablen angepasst werden:

```css
:root {
    --primary: #3498db;      /* Hauptfarbe */
    --secondary: #2ecc71;    /* Sekundärfarbe */
    --danger: #e74c3c;       /* Fehlerfarbe */
}
```

---

## 🔧 Sicherheit

- **Lokale Datenhaltung**: Bei lokaler Nutzung bleiben alle Daten im Browser
- **Server-Option**: Optionale Cloud-Speicherung nur bei Bedarf
- **UID-System**: Einfache Benutzeridentifikation ohne Passwörter
- **HTTPS-Empfehlung**: SSL/TLS für Server-Synchronisation
- **Backup-Schutz**: Automatische Sicherungen bei Server-Verwendung

---

## 🆘 Support

Bei Problemen oder Fragen:

1. **Lokale Probleme**: Überprüfe die Browser-Konsole (F12)
2. **Server-Probleme**: Kontrolliere Logs und Server-Konfiguration
3. **Netzwerk-Fehler**: Teste Netzwerkverbindung und Firewall-Einstellungen
4. **Backup-Probleme**: Verwende lokale Datenbank-Export/Import

### Fehlerbehebung

#### Häufige Probleme:

**❓ Anwendung startet nicht:**
- Überprüfe WebAssembly-Unterstützung in deinem Browser
- Aktiviere JavaScript

**❓ Sync-Fehler:**
- Überprüfe Server-URL und UID in der Konfiguration
- Stelle sicher, dass der Server erreichbar ist

**❓ Datenverlust:**
- Verwende regelmäßige Exporte (empfohlen alle 30 Tage)
- Aktiviere Server-Synchronisation für automatische Backups

---

## 📝 API-Dokumentation

Diese Bereiche betreffen nur Server-Betreiber oder Entwickler:

### Upload-Endpunkt
```
POST https://IhreDomain.com/sync.php
Content-Type: application/json

{
    "action": "upload",
    "uid": "benutzer123",
    "data": {
        "data": "<base64-encoded-sqlite-database>",
        "timestamp": "2025-09-02T21:15:30.000Z",
        "version": "1.0"
    }
}

Response:
{
    "success": true,
    "message": "Daten erfolgreich hochgeladen",
    "uploaded_at": "2025-09-02 22:15:30",
    "filesize": 12345
}
```

### Download-Endpunkt
```
POST https://IhreDomain.com/sync.php
Content-Type: application/json

{
    "action": "download",
    "uid": "benutzer123"
}

Response:
{
    "success": true,
    "data": {
        "data": "<base64-encoded-sqlite-database>",
        "timestamp": "2025-09-02T21:15:30.000Z",
        "version": "1.0"
    },
    "server_timestamp": "2025-09-02 22:15:30"
}
```

---

**verwendet Technologien:**
- **SQL.js**: Clientseitige SQLite-Datenbank mit WebAssembly
- **JavaScript ES6+**: Moderne JavaScript-Features
- **CSS3**: Responsive Design und moderne Stile
- **HTML5**: Semantische Struktur und lokale APIs

99% des Codes wurden KI-basiert generiert
- IDE: Visual Studio Code (macOS)
- KI-Implementation mittels [Roo Code](https://roocode.com)
- LLM: xai/grok-code-fast-1 (RooCode Cloud) / Claude Sonnet-4 ([OpenRouter API](https://openrouter.ai))
---

## 📞 Kontakt

Bei Fragen, Feedback oder Problemen:
- 🐛 GitHub Issues: [Aufgaben-Manager Issues](https://github.com/geimist/Aufgaben-Manager/issues)
- 📖 Dokumentation: Diese README-Datei

