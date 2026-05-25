<?php
$page_title = 'Profilul meu';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();

$pdo = getMySQLPDO();
$mysqli = getMySQLiConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalid. Vă rugăm reîncărcați pagina.';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Email-ul este obligatoriu.';
        } else {
            if ($pdo) {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($stmt->execute([$email, $_SESSION['user_id']])) {
                    $success = 'Profil actualizat cu succes!';
                } else {
                    $error = 'Eroare la actualizarea profilului.';
                }
            }
        }
    }
}

$user = null;
if ($mysqli) {
    $stmt = $mysqli->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}
?>

<h2>Profilul meu</h2>

<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form action="profile.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <fieldset>
        <legend><strong>Informații cont (Exemplu Precompletare)</strong></legend>
        <p>
            <label for="username">Nume utilizator:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
            <small>Numele de utilizator nu poate fi schimbat.</small>
        </p>
        <p>
            <label for="email">Adresă Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        </p>
        <p>
            <label for="role">Rol Sistem:</label>
            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" readonly>
        </p>
        <p>
            <label for="created_at">Data înscrierii:</label>
            <input type="text" id="created_at" name="created_at" value="<?php echo htmlspecialchars($user['created_at'] ?? ''); ?>" readonly>
        </p>
    </fieldset>
    
    <div class="form-buttons">
        <input type="submit" value="Salvează Modificările">
    </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 