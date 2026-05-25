<?php
/**
 * Cerinta 4: Paginare exclusiv server-side (fara JavaScript/jQuery)
 * Navigarea intre pagini se face prin mecanisme server-side (GET parameters + redirects).
 */
$page_title = 'Paginare Server-Side (fara JS)';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();

$mysqli = getMySQLiConnection();

// Parametrii paginare
$limit = 5;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = 0;
$data = [];

if ($mysqli) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM privelisti");
    if ($result) {
        $total = (int)$result->fetch_assoc()['cnt'];
    }

    $stmt = $mysqli->prepare(
        "SELECT p.id, p.titlu, p.descriere, p.judet, p.localitate, p.tip, p.altitudine, p.lat, p.lng, p.website, p.status, p.created_at, u.username
         FROM privelisti p
         JOIN users u ON p.user_id = u.id
         ORDER BY p.id ASC
         LIMIT ? OFFSET ?"
    );

    if ($stmt) {
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    }
    $mysqli->close();
}

$total_pages = (int)ceil($total / $limit);
?>

<h2>Cerința 4: Paginare exclusiv server-side (fără JavaScript)</h2>
<p>
    Această pagină implementează navigarea între pagini <strong>exclusiv pe server</strong>,
    fără niciun fel de JavaScript sau jQuery. Toate butoanele de navigare sunt link-uri
    (elemente <code><a></code>) sau formulare HTML clasice care trimit cereri GET către server.
</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titlu</th>
                <th>Județ</th>
                <th>Localitate</th>
                <th>Tip</th>
                <th>Altitudine (m)</th>
                <th>Status</th>
                <th>Autor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr><td colspan="8" style="text-align:center;">Nu există înregistrări.</td></tr>
            <?php else: ?>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['titlu']); ?></td>
                    <td><?php echo htmlspecialchars($row['judet']); ?></td>
                    <td><?php echo htmlspecialchars($row['localitate']); ?></td>
                    <td><?php echo htmlspecialchars($row['tip']); ?></td>
                    <td><?php echo htmlspecialchars($row['altitudine']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['status']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Navigare server-side: link-uri simple (fara JS) -->
<div style="margin-top: 20px; display: flex; gap: 15px; align-items: center;">

    <!-- Buton Previous: link simplu (disabled = fara href) -->
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" style="
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        ">← Previous <?php echo $limit; ?></a>
    <?php else: ?>
        <span style="
            display: inline-block;
            padding: 10px 20px;
            background: #bdc3c7;
            color: #7f8c8d;
            border-radius: 4px;
            cursor: not-allowed;
            font-weight: bold;
        ">← Previous <?php echo $limit; ?></span>
    <?php endif; ?>

    <!-- Informatii pagina -->
    <span style="font-weight: bold; font-size: 1.1em;">
        Pagina <?php echo $page; ?> / <?php echo max(1, $total_pages); ?>
        (Total: <?php echo $total; ?> înregistrări)
    </span>

    <!-- Buton Next: link simplu (disabled = fara href) -->
    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>" style="
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        ">Next <?php echo $limit; ?> →</a>
    <?php else: ?>
        <span style="
            display: inline-block;
            padding: 10px 20px;
            background: #bdc3c7;
            color: #7f8c8d;
            border-radius: 4px;
            cursor: not-allowed;
            font-weight: bold;
        ">Next <?php echo $limit; ?> →</span>
    <?php endif; ?>

</div>

<!-- Navigare rapida: formular cu select -->
<div style="margin-top: 20px;">
    <form method="get" action="" style="display: inline-flex; gap: 10px; align-items: center;">
        <label for="jump-page" style="font-weight: bold;">Sari la pagina:</label>
        <select id="jump-page" name="page" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($i === $page) ? 'selected' : ''; ?>>Pagina <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <noscript>
            <!-- Fara JS, avem nevoie de submit button -->
            <input type="submit" value="Mergi" style="padding: 5px 15px; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
        </noscript>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
