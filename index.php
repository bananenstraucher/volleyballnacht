<?php
// Datei für Zugriffszähler
$filePath = 'stats.txt';

// XML-Daten einlesen
$xmlFile = "tournament.xml";
if (!file_exists($xmlFile)) {
    die('Fehler: XML-Datei nicht gefunden.');
}
$xml = simplexml_load_file($xmlFile);
if (!$xml) {
    die('Fehler: XML-Datei konnte nicht geladen werden.');
}

// Standardwerte initialisieren
$stats = [
    'QR' => 0,
    'NFC' => 0,
    'Gesamt' => 0
];

// Wenn die Datei existiert, die bisherigen Werte laden
if (file_exists($filePath)) {
    $fileContent = file_get_contents($filePath);
    $lines = explode(PHP_EOL, trim($fileContent));
    foreach ($lines as $line) {
        list($key, $value) = explode(': ', $line);
        $stats[$key] = (int)$value;
    }
}

// Zugriffsquelle ermitteln
if (isset($_GET['source'])) {
    $source = strtolower($_GET['source']); // "qr" oder "nfc"

    if ($source === 'qr') {
        $stats['QR']++;
    } elseif ($source === 'nfc') {
        $stats['NFC']++;
    }

    $stats['Gesamt']++;
    // Datei mit aktualisierten Werten speichern
    $fileContent = "QR: {$stats['QR']}\nNFC: {$stats['NFC']}\nGesamt: {$stats['Gesamt']}";
    file_put_contents($filePath, $fileContent);

    // Weiterleitung zur Hauptseite
    header('Location: https://www.gymnasium-schwarzenberg.de/volleyballnacht/');
}

// Teilnehmer aus der XML extrahieren
$participants = [];
foreach ($xml->GruppeA->Teilnehmer->team as $team) {
    $participants[] = ['name' => (string)$team, 'rank' => 'Unklar']; // Platzierungen noch nicht festgelegt
}

foreach ($xml->GruppeB->Teilnehmer->team as $team) {
    $participants[] = ['name' => (string)$team, 'rank' => 'Unklar']; // Platzierungen noch nicht festgelegt
}

// Offene Spiele ermitteln
$offene_spiele = [];
foreach ($xml->Spiele->children() as $spiel) {
    foreach ($spiel->children() as $group) {
        if ((string)$group['Status'] === 'offen') {
            $offene_spiele[] = [
                'team1' => (string)$group->Team[0],
                'team2' => (string)$group->Team[1],
                'feld' => (string)$group->Feld
            ];
        }
    }
}

// Nächste Spiele auswählen
$nextMatch = $offene_spiele[0] ?? null;
$followingMatch = $offene_spiele[1] ?? null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/volleyballnacht/assets/css/style.css">
    <title>Ballsportnacht</title>
</head>
<body>
    <div class="container">
        <div class="logo"><b><span>Voll</span><span>eyb</span>al<span>lna</span>cht</b></div>
        <div class="eventBox" id="current-event">
            <h2>Aktuelles Spiel</h2>
            <p id="next-match" class="larger">
                <?= $nextMatch ? "{$nextMatch['team1']} VS {$nextMatch['team2']}" : "Kein nächstes Match gefunden." ?>
            </p>
        </div>
        <div class="eventBox" id="next-event">
            <h2>Nächstes Spiel</h2>
            <p id="following-match" class="larger">
                <?= $followingMatch ? "{$followingMatch['team1']} VS {$followingMatch['team2']}" : "Kein darauffolgendes Match gefunden." ?>
            </p>
        </div>
        <iframe src="https://challonge.com/de/volleyballnacht/module?show_standings=1&show_live_status=0&tab=groups" width="100%" height="500" frameborder="0"></iframe>
        <h6 style="text-align: center;">built by <a href="https://www.instagram.com/business_flx.wglt">flx.wglt</a></h6>
    </div>
</body>
</html>
