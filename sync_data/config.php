<?php
/**
 * Konfiguration für sync.php
 */

// ==================== E-Mail Konfiguration ====================
define('CONTACT_EMAIL', 'support@example.com');

// ==================== Backups und Speicher ====================
define('MAX_BACKUPS', 50); // Maximale Anzahl Backup-Versionen pro UID
define('MAX_UPLOAD_SIZE', 52428800); // Maximale Upload-Größe (50MB)

// ==================== Performance ====================
define('MAX_EXECUTION_TIME', 30); // Maximale Ausführungszeit in Sekunden

// ==================== Versionsnummer ====================
define('DEFAULT_VERSION', '1.0'); // Standard-Version für neue UID-Einträge

// ==================== Kontaktformular Validierung ====================
define('NAME_MIN_LENGTH', 2);
define('NAME_MAX_LENGTH', 100);
define('MESSAGE_MIN_LENGTH', 10);
define('MESSAGE_MAX_LENGTH', 1000);

// ==================== Sicherheitscheck ====================
// Beende die Skript-Ausführung, wenn diese Datei direkt aufgerufen wird
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direkter Zugriff auf diese Datei ist nicht erlaubt.');
}
?>