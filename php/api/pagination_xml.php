<?php
/**
 * API endpoint pentru paginare - returneaza date in format XML
 * Cerinta 2: Vanilla JS AJAX cu XML
 *
 * Parametrii GET:
 *   page  - numarul paginii (1-based)
 *   limit - numarul de inregistrari per pagina
 *
 * Raspuns XML:
 *   <response>
 *     <total>N</total>
 *     <page>P</page>
 *     <limit>L</limit>
 *     <total_pages>TP</total_pages>
 *     <data>
 *       <record>...</record>
 *       ...
 *     </data>
 *   </response>
 */

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once dirname(__DIR__) . '/includes/config.php';

// Verificare autentificare
if (!isLoggedIn()) {
    http_response_code(401);
    echo '<?xml version="1.0" encoding="UTF-8"?><response><error>Neautorizat. Va rugam sa va autentificati.</error></response>';
    exit;
}

$mysqli = getMySQLiConnection();
if ($mysqli === null) {
    http_response_code(500);
    echo '<?xml version="1.0" encoding="UTF-8"?><response><error>Eroare la conectarea la baza de date.</error></response>';
    exit;
}

// Parametrii paginare
$page  = isset($_GET['page'])  ? max(1, (int)$_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 5;
$offset = ($page - 1) * $limit;

// Obtine numarul total de inregistrari
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti");
if (!$result) {
    http_response_code(500);
    echo '<?xml version="1.0" encoding="UTF-8"?><response><error>Eroare la interogarea bazei de date.</error></response>';
    exit;
}
$total = (int)$result->fetch_assoc()['cnt'];
$total_pages = (int)ceil($total / $limit);

// Obtine inregistrarile pentru pagina curenta
$stmt = $mysqli->prepare(
    "SELECT p.id, p.titlu, p.descriere, p.judet, p.localitate, p.tip, p.altitudine, p.lat, p.lng, p.website, p.status, p.created_at, u.username
     FROM privelisti p
     JOIN users u ON p.user_id = u.id
     ORDER BY p.id ASC
     LIMIT ? OFFSET ?"
);

if ($stmt === false) {
    http_response_code(500);
    echo '<?xml version="1.0" encoding="UTF-8"?><response><error>Eroare la pregatirea interogarii.</error></response>';
    exit;
}

$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Construim XML-ul
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response></response>');
$xml->addChild('total', $total);
$xml->addChild('page', $page);
$xml->addChild('limit', $limit);
$xml->addChild('total_pages', $total_pages);

$dataNode = $xml->addChild('data');

while ($row = $result->fetch_assoc()) {
    $record = $dataNode->addChild('record');
    foreach ($row as $key => $value) {
        // Escape special XML characters
        $record->addChild($key, htmlspecialchars($value ?? '', ENT_XML1, 'UTF-8'));
    }
}

$stmt->close();
$mysqli->close();

echo $xml->asXML();
