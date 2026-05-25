<?php
$page_title = 'Înregistrare';
require_once dirname(__DIR__) . '/includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalid. Vă rugăm reîncărcați pagina.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['parola'] ?? '';
        $password_confirm = $_POST['parola_confirm'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Vă rugăm completați toate câmpurile.';
        } elseif (strlen($username) < 3) {
            $error = 'Numele de utilizator trebuie să aibă minim 3 caractere.';
        } elseif (strlen($password) < 6) {
            $error = 'Parola trebuie să aibă minim 6 caractere.';
        } elseif ($password !== $password_confirm) {
            $error = 'Parolele nu coincid.';
        } else {
            $mysqli = getMySQLiConnection();
            if ($mysqli) {
                $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $error = 'Numele de utilizator sau email-ul este deja folosit.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'utilizator')");
                    $stmt->bind_param("sss", $username, $email, $hashed_password);
                    if ($stmt->execute()) {
                        $success = 'Cont creat cu succes! Vă puteți autentifica.';
                        header('Refresh: 2; url=login.php');
                    } else {
                        $error = 'Eroare la crearea contului.';
                    }
                }
            }
        }
    }
}
?>

<h2>Creare cont nou</h2>

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

<form action="register.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <fieldset>
        <legend><strong>Date utilizator</strong></legend>
        <p>
            <label for="username">Nume utilizator (minim 3 caractere):</label>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </p>
        <p>
            <label for="email">Adresă Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </p>
        <p>
            <label for="parola">Parolă (minim 6 caractere):</label>
            <input type="password" id="parola" name="parola" required>
        </p>
        <p>
            <label for="parola_confirm">Confirmă Parola:</label>
            <input type="password" id="parola_confirm" name="parola_confirm" required>
        </p>
    </fieldset>
    
    <div class="form-buttons">
        <input type="submit" value="Înregistrare">
    </div>
    
    <p style="margin-top: 20px;">Ai deja un cont? <a href="login.php">Autentifică-te aici</a>.</p>
</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>