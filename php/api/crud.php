<?php
/**
 * API endpoint pentru editare privelisti (CRUD)
 * Cerinta 5 & 6: Vanilla JS si jQuery AJAX edit form
 *
 * GET  ?action=get_all_ids        -> returneaza lista cu toate id-urile si titlurile
 * GET  ?action=get_record&id=X    -> returneaza o inregistrare dupa id
 * POST ?action=update             -> actualizeaza o inregistrare (trimite JSON in body)
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

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {

    case 'get_all_ids':
        // Returneaza toate id-urile si titlurile pentru popularea dropdown-ului
        $result = $mysqli->query("SELECT id, titlu FROM privelisti ORDER BY id ASC");
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Eroare la interogare: ' . $mysqli->error]);
            exit;
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_record':
        // Returneaza o inregistrare dupa id
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalid.']);
            exit;
        }

        $stmt = $mysqli->prepare(
            "SELECT p.id, p.titlu, p.descriere, p.judet, p.localitate, p.tip, p.altitudine, p.lat, p.lng, p.website, p.status, u.username
             FROM privelisti p
             JOIN users u ON p.user_id = u.id
             WHERE p.id = ?"
        );
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Eroare la pregatirea interogarii.']);
            exit;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            echo json_encode(['data' => $row], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Inregistrarea nu a fost gasita.']);
        }
        break;

    case 'update':
        // Actualizeaza o inregistrare
        // Citeste JSON din body
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Date JSON invalide.']);
            exit;
        }

        $id = isset($input['id']) ? (int)$input['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalid.']);
            exit;
        }

        // Verificam CSRF token daca e trimis (optional pentru apeluri AJAX simple)
        // Pentru lab, acceptam fara CSRF dar in productie ar trebui implementat

        $titlu      = $input['titlu'] ?? null;
        $descriere  = $input['descriere'] ?? null;
        $judet      = $input['judet'] ?? null;
        $localitate = $input['localitate'] ?? null;
        $tip        = $input['tip'] ?? null;
        $altitudine = isset($input['altitudine']) ? (int)$input['altitudine'] : null;
        $lat        = isset($input['lat']) ? (float)$input['lat'] : null;
        $lng        = isset($input['lng']) ? (float)$input['lng'] : null;
        $website    = $input['website'] ?? null;

        // Validare campuri obligatorii
        if (empty($titlu)) {
            http_response_code(400);
            echo json_encode(['error' => 'Titlul este obligatoriu.']);
            exit;
        }

        $stmt = $mysqli->prepare(
            "UPDATE privelisti SET titlu=?, descriere=?, judet=?, localitate=?, tip=?, altitudine=?, lat=?, lng=?, website=? WHERE id=?"
        );
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Eroare la pregatirea actualizarii.']);
            exit;
        }

        $stmt->bind_param("sssssiddsi", $titlu, $descriere, $judet, $localitate, $tip, $altitudine, $lat, $lng, $website, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Inregistrarea a fost actualizata cu succes.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Nicio modificare detectata.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Eroare la actualizare: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Actiune necunoscuta. Actiuni valide: get_all_ids, get_record, update']);
        break;
}

$mysqli->close();
