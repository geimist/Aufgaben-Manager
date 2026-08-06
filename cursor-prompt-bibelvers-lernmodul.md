# Cursor-Prompt: Bibelvers-Lernmodul

## Kontext

Ich habe eine bestehende Single-HTML-Anwendung (HTML, CSS, JS und SQL in einer Datei), die bereits über eine Sync-Funktion und ein Server-Gegenstück verfügt, um Daten über mehrere Geräte hinweg zu nutzen. Backup, Export/Import und Sync sind bereits gelöst und **nicht Teil dieser Aufgabe**.

Ich möchte in diese bestehende Anwendung ein neues Modul integrieren: eine Lernanwendung für das Auswendiglernen von Bibelstellen (Zuordnung von Referenz zu Textinhalt).

## Technische Rahmenbedingungen

- Alles bleibt in der einen bestehenden HTML-Datei (HTML, CSS, JS, SQL inline)
- Persistenz über eine SQLite-im-Browser-Lösung (sql.js / WASM), deren Datenbank-Datei als Blob in localStorage abgelegt wird
- Die Anwendung startet **komplett leer** – keine Beispiel-/Seed-Daten
- Der gesamte Content (Verse, Tags, Kollektionen) wird ausschließlich vom Nutzer selbst gepflegt (volles CRUD)

## Datenmodell (relationales SQL-Schema)

**`verses`**
- id
- reference (String, z.B. "Johannes 3,16")
- text
- translation (Kürzel, z.B. "LUT")
- url (Link zur Online-Quelle des Verses, z.B. Bibleserver)
- created_at
- box_level (Spaced-Repetition-Stufe)
- last_reviewed_at
- times_seen
- times_correct

**`tags`**
- id
- name

**`verse_tags`** (m:n Junction-Tabelle)
- verse_id
- tag_id

**`collections`**
- id
- name
- description

**`collection_verses`** (m:n Junction-Tabelle, mit Reihenfolge)
- collection_id
- verse_id
- position

**`practice_log`** (für spätere Auswertung, optional beim MVP, aber Tabelle bitte anlegen)
- id
- verse_id
- mode (quiz / luckentext / memory)
- result (correct / incorrect)
- timestamp

## Funktionsbereiche

### 1. Verwaltung
- CRUD für Verse (inkl. Referenz, Text, Übersetzung, URL)
- CRUD für Tags, inklusive Autovervollständigung/Vorschlagsliste bereits vorhandener Tags beim Anlegen (um Dubletten wie "Trost"/"trost" zu vermeiden)
- CRUD für Kollektionen inkl. Zuordnung und Sortierung (position) der enthaltenen Verse
- Zuordnung von Versen zu beliebig vielen Tags und Kollektionen (m:n)

### 2. Dashboard
- Übersicht der heute fälligen Verse (basierend auf Spaced-Repetition-Logik)
- Fortschrittsüberblick (z.B. Anzahl Verse gesamt, Anzahl gelernt/in Wiederholung, ggf. gruppiert nach Tag oder Kollektion)

### 3. Übungsmodus
Vor Start einer Übungssitzung wählt der Nutzer:
- **Filter**: nach Tag, nach Kollektion, oder "alle fälligen Verse"
- **Modus**: Quiz (Multiple Choice), Lückentext/Tippen, optional Memory (Paare finden)

**Quiz-Modus – Distraktor-Logik** (3 falsche Antworten zur korrekten Antwort):
1. Priorität 1: Verse mit den meisten gemeinsamen Tags zum aktuellen Vers
2. Priorität 2: Verse aus derselben Kollektion, falls Priorität 1 nicht genug Kandidaten liefert
3. Priorität 3 (Fallback): zufällige Verse aus dem Gesamtpool, damit auch bei kleinem/neuem Datenbestand immer 3 Distraktoren zustande kommen

**Lückentext-Modus**: Verse wird zunehmend ausgeblendet (abhängig vom Box-Level bzw. Fortschritt), Nutzer tippt fehlende Wörter

**Memory-Modus** (optional/nachrangig): klassisches Paare-Finden zwischen Referenz-Karte und Text-Karte, sinnvoll begrenzt auf 4–8 Paare pro Runde

### 4. Spaced-Repetition-Lernmotor
- Jeder Vers hat einen `box_level` (z.B. 0–5), der bestimmt, wie lange bis zur nächsten fälligen Wiederholung vergeht (z.B. täglich → alle 3 Tage → wöchentlich → monatlich)
- Bei richtiger Antwort steigt der box_level, bei falscher Antwort sinkt er (bzw. zurück auf 0)
- `last_reviewed_at` und `times_seen`/`times_correct` werden bei jeder Übungseinheit aktualisiert
- Die Auswahl der "fälligen" Verse für Dashboard und Übungsmodus basiert auf `box_level` + `last_reviewed_at`

## Nicht Teil dieser Aufgabe
- Backup/Export/Import (bereits in der bestehenden Anwendung vorhanden)
- Sync zwischen Geräten (bereits vorhanden)
- Server-Anbindung (bereits vorhanden)

## Aufgabe
Integriere dieses Modul (Datenbankschema, Verwaltungsoberfläche, Dashboard, Übungsmodi inkl. Spaced-Repetition-Logik) in die bestehende Single-HTML-Anwendung. Halte dich an die bestehende Code-Struktur der Anwendung (gleiche Konventionen für Namensgebung, Styling, sql.js-Anbindung etc.), sofern diese aus dem bestehenden Code ersichtlich ist.
