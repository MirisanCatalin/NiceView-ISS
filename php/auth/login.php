<?php
$page_title = 'Login';
require_once dirname(__DIR__) . '/includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalid. Vă rugăm reîncărcați pagina.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['parola'] ?? '';
        $captcha = $_POST['captcha'] ?? '';
        $remember = isset($_POST['retine']);
        $selected_role = $_POST['tip_cont'] ?? 'utilizator';
        
        if (empty($username) || empty($password)) {
            $error = 'Vă rugăm completați toate câmpurile.';
        } elseif (!verifyCaptcha($captcha)) {
            $error = 'Codul CAPTCHA este incorect.';
        } else {
            if (login($username, $password, $remember)) {
                if ($_SESSION['role'] !== $selected_role) {
                    $error = 'Nu aveți drepturi de ' . htmlspecialchars($selected_role) . ' pentru acest cont.';
                    logout(); // Log out if role mismatch
                } else {
                    header('Location: ../user/dashboard.php');
                    exit;
                }
            } else {
                $error = 'Numele de utilizator sau parola sunt incorecte.';
            }
        }
    }
}

checkRememberMe();
if (isLoggedIn()) {
    header('Location: ../user/dashboard.php');
    exit;
}

// Generate fresh CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<h2>Autentificare</h2>

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

<form action="login.php" method="post" name="formular_login">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

    <fieldset>
        <legend><strong>Date de autentificare</strong></legend>

        <p>
            <label for="username">Nume utilizator (min. 3 caractere):</label>
            <input type="text" id="username" name="username" maxlength="30" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </p>

        <p>
            <label for="parola">Parolă (min. 6 caractere):</label>
            <input type="password" id="parola" name="parola" maxlength="50" required>
        </p>

        <p>
            <label for="captcha">Cod CAPTCHA:</label>
            <input type="text" id="captcha" name="captcha" maxlength="6" required style="width: 150px; display: inline-block;">
            <img src="captcha.php?t=<?php echo time(); ?>" alt="CAPTCHA" style="vertical-align: middle; margin-left: 10px; border: 1px solid #ccc;">
            <small style="display: block; margin-top: 5px; color: #666;">Dacă nu puteți citi codul, <a href="login.php">reîncărcați pagina</a>.</small>
        </p>

        <p>
            <label><input type="checkbox" name="retine" value="da"> Reține-mă</label>
        </p>
    </fieldset>

    <fieldset>
        <legend><strong>Tip cont</strong></legend>
        <label><input type="radio" name="tip_cont" value="utilizator" checked> Utilizator</label>
        <label><input type="radio" name="tip_cont" value="administrator"> Administrator</label>
    </fieldset>

    <div class="form-buttons">
        <input type="submit" value="Autentificare">
        <input type="reset" value="Resetează">
    </div>

    <p style="margin-top: 20px;"><a href="../contact.php">Ai uitat parola?</a></p>
    <p>Nu ai un cont? <a href="register.php">Înregistrează-te aici</a>.</p>

</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>