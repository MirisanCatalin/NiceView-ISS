<?php
$page_title = 'Dashboard';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();

$mysqli = getMySQLiConnection();
$pdo = getMySQLPDO();

$user_count = 0;
$priveliste_count = 0;
$aprobate_count = 0;
$in_asteptare_count = 0;
$judete_active = 0;

if ($mysqli) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM users");
    $user_count = $result ? $result->fetch_assoc()['cnt'] : 0;
    
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti");
    $priveliste_count = $result ? $result->fetch_assoc()['cnt'] : 0;
    
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti WHERE status='aprobat'");
    $aprobate_count = $result ? $result->fetch_assoc()['cnt'] : 0;
    
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti WHERE status='in_asteptare'");
    $in_asteptare_count = $result ? $result->fetch_assoc()['cnt'] : 0;
    
    $result = $mysqli->query("SELECT COUNT(DISTINCT judet) as cnt FROM privelisti WHERE judet IS NOT NULL");
    $judete_active = $result ? $result->fetch_assoc()['cnt'] : 0;
}

$recent_activity = [];
if ($mysqli) {
    $result = $mysqli->query("SELECT p.*, u.username FROM privelisti p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");
    if ($result) {
        $recent_activity = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<h2>Dashboard</h2>

<div class="cards">
    <div class="card">
        <h3>Utilizatori</h3>
        <p class="card-number"><?php echo $user_count; ?></p>
    </div>
    <div class="card">
        <h3>Priveliști</h3>
        <p class="card-number"><?php echo $priveliste_count; ?></p>
    </div>
    <div class="card">
        <h3>Aprobate</h3>
        <p class="card-number"><?php echo $aprobate_count; ?></p>
    </div>
    <div class="card">
        <h3>În așteptare</h3>
        <p class="card-number"><?php echo $in_asteptare_count; ?></p>
    </div>
    <div class="card">
        <h3>Județe active</h3>
        <p class="card-number"><?php echo $judete_active; ?></p>
    </div>
    <div class="card">
        <h3>Rolul tău</h3>
        <p class="card-number" style="font-size: 1.2rem;"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
    </div>
</div>

<h2>Activitate recentă în platformă</h2>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Utilizator</th>
                <th>Titlu Priveliște</th>
                <th>Județ</th>
                <th>Tip</th>
                <th>Altitudine</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_activity as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['titlu']); ?></td>
                <td><?php echo htmlspecialchars($row['judet']); ?></td>
                <td><?php echo htmlspecialchars($row['tip']); ?></td>
                <td><?php echo htmlspecialchars($row['altitudine']); ?> m</td>
                <td><strong><?php echo htmlspecialchars($row['status']); ?></strong></td>
                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_activity)): ?>
                <tr><td colspan="7">Nu există activitate recentă.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<section style="margin-top: 30px;">
    <h2>Link-uri utile</h2>
    <div class="cards" style="background: none; box-shadow: none; padding: 0;">
        <a href="adauga.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Adaugă</h3>
            <p>Priveliște nouă</p>
        </a>
        <a href="upload.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Upload</h3>
            <p>Gestionare fișiere</p>
        </a>
        <a href="profile.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Profil</h3>
            <p>Setări cont</p>
        </a>
    </div>
</section>

<!-- Laborator AJAX - Link-uri cerinte -->
<section style="margin-top: 30px; padding: 20px; background: #f0f8ff; border-radius: 8px; border: 2px solid #3498db;">
    <h2 style="color: #2c3e50;">🧪 Laborator AJAX</h2>
    <p style="margin-bottom: 15px;">Navigați către paginile de mai jos pentru a testa cerințele implementate:</p>
    <div class="cards" style="background: none; box-shadow: none; padding: 0;">

        <a href="paginare_json.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #27ae60;">
            <h3>Cerința 1</h3>
            <p>Paginare Vanilla JS + JSON</p>
        </a>
        <a href="paginare_xml.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #e67e22;">
            <h3>Cerința 2</h3>
            <p>Paginare Vanilla JS + XML</p>
        </a>
        <a href="paginare_jquery.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #9b59b6;">
            <h3>Cerința 3</h3>
            <p>Paginare jQuery + JSON</p>
        </a>
        <a href="paginare_serverside.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #e74c3c;">
            <h3>Cerința 4</h3>
            <p>Paginare Server-Side</p>
        </a>
        <a href="editare_vanilla.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #1abc9c;">
            <h3>Cerința 5</h3>
            <p>Editare Vanilla JS AJAX</p>
        </a>
        <a href="editare_jquery.php" class="card" style="text-decoration: none; color: inherit; border: 1px solid #f39c12;">
            <h3>Cerința 6</h3>
            <p>Editare jQuery AJAX</p>
        </a>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>