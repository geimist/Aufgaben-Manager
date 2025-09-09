<?php
/**
 * ToDo Manager - Server Sync Script
 * Einfache PHP-basierte Synchronisation der lokalen Datenbank
 *
 * Sicherheit: UID-basierte Zugangskontrolle
 * Die Daten werden pro UID in separaten Ordnern gespeichert
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
// Inkludiere Konfigurationsdatei
require_once(__DIR__ . '/sync_data/config.php');


// Handle preflight OPTIONS request - ohne JSON-Response für Unsichtbarkeit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Nur POST-Requests akzeptieren
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

// Fehlerbehandlung aktivieren
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Maximale Ausführungszeit erhöhen
set_time_limit(MAX_EXECUTION_TIME);

// POST-Daten empfangen - nur valide JSON-Daten akzeptieren
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Bei ungültiger JSON oder fehlenden Daten: 404-Fehler als HTML
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    header('Content-Type: text/html');
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

// Parameter validieren - fehlende action: 404
if (!isset($data['action'])) {
    header('Content-Type: text/html');
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

$action = $data['action'];
$uid = $data['uid'] ?? '';

// UID muss vorhanden sein
if (empty($uid)) {
    header('Content-Type: text/html');
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

// UID validieren (nur alphanumerisch + _ -)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $uid)) {
    header('Content-Type: text/html');
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

// Ordner für UID erstellen
$userDir = __DIR__ . '/sync_data/' . $uid . '/';
if (!is_dir($userDir)) {
    if (!mkdir($userDir, 0755, true)) {
        header('Content-Type: text/html');
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
        exit;
    }
}

// INDEX-Datei für UID erstellen/finden
$indexFile = $userDir . 'index.json';
if (!file_exists($indexFile)) {
    file_put_contents($indexFile, json_encode([
        'uid' => $uid,
        'created' => date('Y-m-d H:i:s'),
        'last_sync' => null,
        'version' => DEFAULT_VERSION
    ]));
}

try {
    switch ($action) {
        case 'upload':
            uploadDatabase($userDir, $data);
            break;

        case 'download':
            downloadDatabase($userDir, $uid);
            break;

        case 'list_versions':
            listVersions($userDir);
            break;

        case 'restore_version':
            restoreVersion($userDir, $data);
            break;

        case 'delete_version':
            deleteVersion($userDir, $data);
            break;

        case 'send_mail':
            handleSendMail($data);
            break;

        default:
            // Unbekannte Aktion: 404
            header('Content-Type: text/html');
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
            exit;
    }
} catch (Exception $e) {
    // Server-Fehler: 404 für mehr Unsichtbarkeit
    header('Content-Type: text/html');
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
    exit;
}

// Upload-Funktion
function uploadDatabase($userDir, $data) {
    if (!isset($data['data'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Keine Daten zum Hochladen'
        ]);
        exit;
    }

    $uploadData = $data['data'];

    // Daten validieren
    if (!isset($uploadData['data']) || !isset($uploadData['timestamp'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Datenstruktur'
        ]);
        exit;
    }

    // last_local_change vom Client empfangen
    $clientLastChange = $uploadData['last_local_change'] ?? null;
    $deviceFingerprint = $uploadData['deviceFingerprint'] ?? null;

    // Größe prüfen (max MAX_UPLOAD_SIZE bytes)
    $dataSize = strlen($uploadData['data']) * 0.75; // Base64-Überkopf berücksichtigen
    if ($dataSize > MAX_UPLOAD_SIZE) { // Maximale Upload-Größe
        header('Content-Type: text/html');
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested resource was not found on this server.</p></body></html>';
        exit;
    }

    // Dateiname mit Zeitstempel erstellen
    $timestamp = preg_replace('/[^0-9]/', '', $uploadData['timestamp']);
    $filename = 'db_' . date('Ymd_His') . '_' . substr($timestamp, 0, 14) . '.json';
    $filepath = $userDir . $filename;

// DEBUG: Logge Zeitstempel-Unterschiede
error_log("DEBUG Upload - Client timestamp: " . $uploadData['timestamp'] . ", Server upload time: " . date('Y-m-d H:i:s'));
if (isset($uploadData['data']['data'])) {
    // Versuche die letzte Datenänderung aus den Nutzdaten zu extrahieren, wenn verfügbar
    $rawData = json_decode($uploadData['data']['data'], true);
    if ($rawData && isset($rawData['last_change'])) {
        error_log("DEBUG Upload - Extracted data last_change: " . $rawData['last_change']);
    }
}

    // Daten speichern
    $fileContent = json_encode([
        'metadata' => [
            'uploaded_at' => date('Y-m-d H:i:s'),
            'client_timestamp' => $uploadData['timestamp'],
            'last_local_change' => $clientLastChange,
            'version' => $uploadData['version'] ?? 'unknown',
            'deviceFingerprint' => $deviceFingerprint,
            'filesize' => strlen($uploadData['data'])
        ],

        'data' => $uploadData
    ], JSON_PRETTY_PRINT);

    if (file_put_contents($filepath, $fileContent) === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Speichern der Daten'
        ]);
        exit;
    }

    // Backup der letzten Version erstellen
    $files = glob($userDir . 'db_*.json');
    if (count($files) > 1) {
        // Älteste Dateien löschen (behalte nur die letzten MAX_BACKUPS)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        while (count($files) > MAX_BACKUPS) {
            $oldFile = array_pop($files);
            unlink($oldFile);
        }

        // Neueste als current.json markieren
        $currentFile = $userDir . 'current.json';
        copy($filepath, $currentFile);
    } else {
        // Erste Upload - als current.json speichern
        copy($filepath, $userDir . 'current.json');
    }

    // Index aktualisieren
    updateUserIndex($userDir, 'last_sync', date('Y-m-d H:i:s'));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Daten erfolgreich hochgeladen',
        'uploaded_at' => date('Y-m-d H:i:s'),
        'filesize' => strlen($uploadData['data'])
    ]);
}

// Download-Funktion
function downloadDatabase($userDir, $uid) {
    $currentFile = $userDir . 'current.json';

    if (!file_exists($currentFile)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => null,
            'message' => 'Keine Daten gefunden für diese UID'
        ]);
        return;
    }

    // Daten laden
    $fileContent = file_get_contents($currentFile);
    if ($fileContent === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Laden der Daten'
        ]);
        exit;
    }

    $fileData = json_decode($fileContent, true);
    if (!$fileData || !isset($fileData['data'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Datenstruktur'
        ]);
        exit;
    }

    header('Content-Type: application/json');

    // DEBUG: Logge den zurückgegebenen Zeitstempel für Vergleich
    error_log("DEBUG Download - Returning server_timestamp (last_local_change): " . ($fileData['metadata']['last_local_change'] ?? $fileData['metadata']['uploaded_at']));
    error_log("DEBUG Download - Client timestamp from data: " . ($fileData['data']['timestamp'] ?? 'N/A'));

    // Verwende last_local_change statt uploaded_at für richtige Synchronisation
    $serverTimestamp = $fileData['metadata']['last_local_change'] ?? $fileData['metadata']['uploaded_at'];

    echo json_encode([
        'success' => true,
        'data' => $fileData['data'],
        'message' => 'Daten erfolgreich geladen',
        'server_timestamp' => $serverTimestamp,
        'deviceFingerprint' => $fileData['metadata']['deviceFingerprint'] ?? null
    ]);
}

// Hilfsfunktion: User-Index aktualisieren
function updateUserIndex($userDir, $key, $value) {
    $indexFile = $userDir . 'index.json';

    if (file_exists($indexFile)) {
        $index = json_decode(file_get_contents($indexFile), true);
        $index[$key] = $value;
        file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT));
    }
}

// List versions function
function listVersions($userDir) {
    $files = glob($userDir . 'db_*.json');

    if (empty($files)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'versions' => [],
            'message' => 'Keine Versionen gefunden'
        ]);
        return;
    }

    // Sortiere nach Änderungszeit (neueste zuerst)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $versions = [];
    foreach ($files as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if ($data && isset($data['metadata'])) {
            $versions[] = [
                'filename' => $filename,
                'uploaded_at' => $data['metadata']['uploaded_at'] ?? 'unbekannt',
                'client_timestamp' => $data['metadata']['client_timestamp'] ?? 'unbekannt',
                'last_local_change' => $data['metadata']['last_local_change'] ?? '',
                'version' => $data['metadata']['version'] ?? 'unbekannt',
                'deviceFingerprint' => $data['metadata']['deviceFingerprint'] ?? 'Unbekannt',
                'is_current' => file_exists($userDir . 'current.json') && $data['data'] === json_decode(file_get_contents($userDir . 'current.json'), true)['data'] ?? false
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'versions' => $versions,
        'message' => count($versions) . ' Versionen gefunden'
    ]);
}

// Restore version function
function restoreVersion($userDir, $data) {
    if (!isset($data['filename'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Fehlender Dateiname zum Wiederherstellen'
        ]);
        exit;
    }

    $filename = $data['filename'];
    $filepath = $userDir . $filename;

    // Prüfe, ob Datei existiert
    if (!file_exists($filepath)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Version nicht gefunden'
        ]);
        exit;
    }

    // Kopiere als neue current.json
    $currentFile = $userDir . 'current.json';
    if (copy($filepath, $currentFile) === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Wiederherstellen der Version'
        ]);
        exit;
    }

    // Index aktualisieren
    updateUserIndex($userDir, 'last_sync', date('Y-m-d H:i:s'));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Version erfolgreich wiederhergestellt',
        'restored_at' => date('Y-m-d H:i:s')
    ]);
}

// Delete version function
function deleteVersion($userDir, $data) {
    if (!isset($data['filename'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Fehlender Dateiname zum Löschen'
        ]);
        exit;
    }

    $filename = $data['filename'];
    $filepath = $userDir . $filename;

    // Prüfe, ob Datei existiert
    if (!file_exists($filepath)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Version nicht gefunden'
        ]);
        exit;
    }

    // Current.json nicht löschen
    $currentFile = $userDir . 'current.json';
    if ($filepath === $currentFile) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Aktuelle Version kann nicht gelöscht werden'
        ]);
        exit;
    }

    // Datei löschen
    if (unlink($filepath) === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Löschen der Version'
        ]);
        exit;
    }

    // Index aktualisieren
    updateUserIndex($userDir, 'last_sync', date('Y-m-d H:i:s'));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Version erfolgreich gelöscht',
        'deleted_at' => date('Y-m-d H:i:s')
    ]);
}

// Handle send mail for contact form
function handleSendMail($data) {
    // Validate required fields
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Alle Felder müssen ausgefüllt sein'
        ]);
        exit;
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $message = trim($data['message']);

    // Validate input lengths
    if (strlen($name) < NAME_MIN_LENGTH || strlen($name) > NAME_MAX_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Name muss zwischen ' . NAME_MIN_LENGTH . ' und ' . NAME_MAX_LENGTH . ' Zeichen haben'
        ]);
        exit;
    }

    if (strlen($message) < MESSAGE_MIN_LENGTH || strlen($message) > MESSAGE_MAX_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nachricht muss zwischen ' . MESSAGE_MIN_LENGTH . ' und ' . MESSAGE_MAX_LENGTH . ' Zeichen haben'
        ]);
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige E-Mail-Adresse'
        ]);
        exit;
    }

    // Sanitize inputs
    $name = strip_tags($name);
    $message = strip_tags($message);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Prepare email
    $subject = 'Kontaktformular Aufgabenplaner: Nachricht von ' . $name;
    $emailBody = "Name: $name\nE-Mail: $email\n\nNachricht:\n$message\n\nGesendet am: " . date('Y-m-d H:i:s');

    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email with detailed error logging
    try {
        error_log("DEBUG Mail: Preparing to send from $email to " . CONTACT_EMAIL);
        error_log("DEBUG Mail: Subject: $subject");

        $mailResult = mail(CONTACT_EMAIL, $subject, $emailBody, $headers);

        if ($mailResult) {
            error_log("DEBUG Mail: Successfully sent");
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Nachricht erfolgreich gesendet'
            ]);
        } else {
            error_log("DEBUG Mail: mail() returned false");
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'mail() function returned false - check server configuration',
                'debug' => 'Mail function failed - no specific error available in PHP CLI mode'
            ]);
        }
    } catch (Exception $e) {
        error_log("DEBUG Mail: Exception caught: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Senden der Nachricht: ' . $e->getMessage(),
            'debug' => $e->getMessage()
        ]);
    }
}

?>
