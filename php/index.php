<?php
$page_title = 'Acasă';
require_once __DIR__ . '/includes/header.php';

checkRememberMe();

$mysqli = getMySQLiConnection();
$sqlite = getSQLitePDO();

$site_description = 'Platforma pentru gestionarea priveliștilor turistice';
if ($sqlite) {
    $result = $sqlite->query("SELECT setting_value FROM settings WHERE setting_key='site_description'");
    $site_description = $result ? $result->fetchColumn() : $site_description;
}

$privelisti = [];
$stats = ['cnt' => 0];
$total_utilizatori = 0;
$total_privelisti = 0;

if ($mysqli) {
    $result = $mysqli->query("SELECT * FROM privelisti WHERE status='aprobat' ORDER BY created_at DESC LIMIT 10");
    if ($result) {
        $privelisti = $result->fetch_all(MYSQLI_ASSOC);
    }
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti WHERE status='aprobat'");
    $stats = $result ? $result->fetch_assoc() : ['cnt' => 0];
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM users");
    $total_utilizatori = $result ? $result->fetch_assoc()['cnt'] : 0;
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti");
    $total_privelisti = $result ? $result->fetch_assoc()['cnt'] : 0;
}
?>

<div id="carousel-container">
    <div id="carousel-slide">
        <img src="images/munte.jpg" id="carousel-img" alt="Carousel Image">
        <a href="#" id="carousel-link">Descoperă frumusețile naturii</a>
    </div>
    <button id="carousel-prev">&#10094;</button>
    <button id="carousel-next">&#10095;</button>
    <button id="carousel-playpause">Pause</button>
</div>

<section>
    <h2>Tipuri de priveliști</h2>
    <ul id="lista-colapsabila">
        <li class="expandabil deschis">Munți
            <ul>
                <li>Vârfuri înalte</li>
                <li>Lacuri glaciare</li>
                <li>Cascade</li>
            </ul>
        </li>
        <li class="expandabil inchis">Mare
            <ul style="display:none">
                <li>Plaje</li>
                <li>Faleze</li>
            </ul>
        </li>
        <li class="expandabil inchis">Lac
            <ul style="display:none">
                <li>Lacuri glaciare</li>
                <li>Lacuri de baraj</li>
            </ul>
        </li>
        <li>Oraș</li>
    </ul>
</section>

<section id="privelisti">
    <h2>Priveliști populare</h2>
    <?php if (empty($privelisti)): ?>
        <p>Nu există priveliști aprobate momentan.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Județ</th>
                        <th>Tip</th>
                        <th>Altitudine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($privelisti as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['titlu']); ?></td>
                        <td><?php echo htmlspecialchars($p['judet']); ?></td>
                        <td><?php echo htmlspecialchars($p['tip']); ?></td>
                        <td><?php echo htmlspecialchars($p['altitudine']); ?> m</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section id="statistici">
    <h2>Statistici Platformă</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Total utilizatori</th>
                    <th>Total priveliști</th>
                    <th>Aprobate</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $total_utilizatori; ?></td>
                    <td><?php echo $total_privelisti; ?></td>
                    <td><?php echo $stats['cnt']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>