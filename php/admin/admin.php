<?php
$page_title = 'Admin Panel';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$pdo = getMySQLPDO();
$mysqli = getMySQLiConnection();
$sqlite = getSQLitePDO();

$success_msg = '';
$error_msg = '';

// Handle MySQL Actions (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Token CSRF invalid.';
    } else {
        if (isset($_POST['action'], $_POST['id'])) {
            $id = (int)$_POST['id'];
            if ($_POST['action'] === 'approve' && $pdo) {
                $stmt = $pdo->prepare("UPDATE privelisti SET status='aprobat' WHERE id=?");
                $stmt->execute([$id]);
                $success_msg = 'Priveliște aprobată.';
            } elseif ($_POST['action'] === 'reject' && $pdo) {
                $stmt = $pdo->prepare("UPDATE privelisti SET status='respins' WHERE id=?");
                $stmt->execute([$id]);
                $success_msg = 'Priveliște respinsă.';
            }
        }

        // Handle SQLite Actions (Update Settings)
        if (isset($_POST['update_settings'])) {
            $site_name = trim($_POST['site_name'] ?? '');
            $site_desc = trim($_POST['site_description'] ?? '');
            
            if ($sqlite && !empty($site_name)) {
                try {
                    $stmt = $sqlite->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_name'");
                    $stmt->execute([$site_name]);
                    $stmt = $sqlite->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_description'");
                    $stmt->execute([$site_desc]);
                    $success_msg = 'Setări site actualizate (în SQLite).';
                } catch (PDOException $e) {
                    $error_msg = 'Eroare SQLite: ' . $e->getMessage();
                }
            }
        }
    }
}

$privelisti = [];
if ($mysqli) {
    $result = $mysqli->query("SELECT p.*, u.username FROM privelisti p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
    if ($result) {
        $privelisti = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$users = [];
if ($pdo) {
    $users = $pdo->query("SELECT id, username, email, role FROM users")->fetchAll();
}

$site_name = '';
$site_desc = '';
if ($sqlite) {
    $site_name = $sqlite->query("SELECT setting_value FROM settings WHERE setting_key='site_name'")->fetchColumn();
    $site_desc = $sqlite->query("SELECT setting_value FROM settings WHERE setting_key='site_description'")->fetchColumn();
}
?>

<h2>Admin Panel</h2>

<?php if ($success_msg): ?>
    <p class="success" style="color: #27ae60; background: #eafaf1; padding: 15px; border-radius: 4px; border-left: 5px solid #27ae60; margin-bottom: 20px;">
        <?php echo htmlspecialchars($success_msg); ?>
    </p>
<?php endif; ?>

<?php if ($error_msg): ?>
    <p class="error" style="color: #c0392b; background: #fdecea; padding: 15px; border-radius: 4px; border-left: 5px solid #c0392b; margin-bottom: 20px;">
        <?php echo htmlspecialchars($error_msg); ?>
    </p>
<?php endif; ?>

<section>
    <h2>Setări Generale (Stocate în SQLite)</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
        <fieldset>
            <legend><strong>Configurare Site</strong></legend>
            <p>
                <label for="site_name">Nume Site:</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
            </p>
            <p>
                <label for="site_description">Descriere Site:</label>
                <input type="text" id="site_description" name="site_description" value="<?php echo htmlspecialchars($site_desc); ?>">
            </p>
        </fieldset>
        <div class="form-buttons">
            <input type="submit" name="update_settings" value="Salvează în SQLite">
        </div>
    </form>
</section>

<section style="margin-top: 30px;">
    <h2>Administrare Priveliști (MySQL)</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Titlu</th>
                    <th>Utilizator</th>
                    <th>Județ</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($privelisti as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['titlu']); ?></td>
                    <td><?php echo htmlspecialchars($p['username'] ?? 'Anonim'); ?></td>
                    <td><?php echo htmlspecialchars($p['judet']); ?></td>
                    <td><strong><?php echo htmlspecialchars($p['status']); ?></strong></td>
                    <td>
                        <?php if ($p['status'] === 'in_asteptare'): ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="submit" value="Aprobă">
                            </form>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="submit" value="Respinge" class="btn-reset">
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section style="margin-top: 30px;">
    <h2>Utilizatori Sistem (MySQL)</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>