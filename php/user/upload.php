<?php
$page_title = 'Upload Fișiere';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();

$pdo = getMySQLPDO();

$error = '';
$success = '';
$files = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalid. Vă rugăm reîncărcați pagina.';
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];
        $max_size = 5 * 1024 * 1024;
        
        if ($_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['upload_file']['size'] > $max_size) {
                $error = 'Fișierul este prea mare (Maxim 5MB).';
            } else {
                // Validate both MIME type AND extension for defense in depth
                $original_name = basename($_FILES['upload_file']['name']);
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // Verify MIME type via finfo (not just client-provided type)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected_mime = $finfo->file($_FILES['upload_file']['tmp_name']);
                
                if (!in_array($extension, $allowed_extensions)) {
                    $error = 'Extensia de fișier nu este permisă.';
                } elseif (!in_array($detected_mime, $allowed_types)) {
                    $error = 'Tipul real de fișier (' . htmlspecialchars($detected_mime) . ') nu este permis.';
                } else {
                    if (!is_dir(UPLOAD_PATH)) {
                        mkdir(UPLOAD_PATH, 0755, true);
                    }
                    
                    // Generate safe filename with only allowed extension
                    $filename = uniqid('file_') . '.' . $extension;
                    $file_path = UPLOAD_PATH . $filename;
                    
                    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $file_path)) {
                        if ($pdo) {
                            $stmt = $pdo->prepare("INSERT INTO uploads (user_id, filename, original_name, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$_SESSION['user_id'], $filename, $original_name, $file_path, $detected_mime]);
                        }
                        $success = 'Fișierul a fost încărcat cu succes!';
                    } else {
                        $error = 'Eroare la salvarea fișierului pe server.';
                    }
                }
            }
        } else {
            $error = 'Eroare la încărcare: ' . $_FILES['upload_file']['error'];
        }
    }
}

if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $files = $stmt->fetchAll();
}

if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    if (!verifyCSRFToken($_GET['csrf_token'])) {
        $error = 'Token CSRF invalid pentru ștergere fișier.';
    } else {
        $file_id = (int)$_GET['delete'];
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ? AND user_id = ?");
            $stmt->execute([$file_id, $_SESSION['user_id']]);
            $file = $stmt->fetch();
            
            if ($file) {
                // Prevent path traversal: validate the path is inside uploads dir
                $real_upload_path = realpath(UPLOAD_PATH);
                $real_file_path = realpath($file['file_path']);
                if ($real_file_path && strpos($real_file_path, $real_upload_path) === 0) {
                    unlink($real_file_path);
                }
                $stmt = $pdo->prepare("DELETE FROM uploads WHERE id = ?");
                $stmt->execute([$file_id]);
                $success = 'Fișierul a fost șters permanent.';
            }
        }
    }
    header('Location: upload.php');
    exit;
}
?>

<h2>Gestionare Fișiere</h2>

<?php if ($error): ?>
    <p class="error" style="color: #c0392b; background: #fdecea; padding: 15px; border-radius: 4px; border-left: 5px solid #c0392b; margin-bottom: 20px;">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success" style="color: #27ae60; background: #eafaf1; padding: 15px; border-radius: 4px; border-left: 5px solid #27ae60; margin-bottom: 20px;">
        <?php echo htmlspecialchars($success); ?>
    </p>
<?php endif; ?>

<section>
    <h2>Încarcă un fișier nou</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
        <fieldset>
            <legend><strong>Selectează fișier</strong></legend>
            <p>
                <label for="upload_file">Fișier (Imagini, PDF, TXT | max 5MB):</label>
                <input type="file" id="upload_file" name="upload_file" required>
            </p>
        </fieldset>
        
        <div class="form-buttons">
            <input type="submit" value="Încarcă fișier">
        </div>
    </form>
</section>

<section style="margin-top: 30px;">
    <h2>Fișierele tale</h2>
    <?php if (empty($files)): ?>
        <p>Nu ai încărcat niciun fișier până acum.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nume Fișier</th>
                        <th>Tip</th>
                        <th>Data Încărcării</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                        <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                        <td><?php echo htmlspecialchars($file['uploaded_at']); ?></td>
                        <td>
                            <a href="upload.php?delete=<?php echo $file['id']; ?>&csrf_token=<?php echo urlencode(generateCSRFToken()); ?>" onclick="return confirm('Sigur doriți să ștergeți acest fișier?');">Șterge</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>