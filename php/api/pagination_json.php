<?php
/**
 * API endpoint pentru paginare - returneaza date in format JSON
 * Cerinta 1: Vanilla JS AJAX cu JSON
 * Cerinta 3: jQuery AJAX (acelasi endpoint)
 *
 * Parametrii GET:
 *   page  - numarul paginii (1-based)
 *   limit - numarul de inregistrari per pagina
 *
 * Raspuns JSON:
 *   { "data": [...], "total": N, "page": P, "limit": L, "total_pages": TP }
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once dirname(__DIR__) . '/includes/config.php';

// Verificare autentificare
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Neautorizat. Va rugam sa va autentificati.']);
    exit;
}

$mysqli = getMySQLiConnection();
if ($mysqli === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare la conectarea la baza de date.']);
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
    echo json_encode(['error' => 'Eroare la interogarea bazei de date: ' . $mysqli->error]);
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
    echo json_encode(['error' => 'Eroare la pregatirea interogarii: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode([
    'data'        => $data,
    'total'       => $total,
    'page'        => $page,
    'limit'       => $limit,
    'total_pages' => $total_pages
], JSON_UNESCAPED_UNICODE);
