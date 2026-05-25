<?php
/**
 * SQL Injection - Pagina Vulnerabila (Laborator Securitate)
 * 
 * VULNERABILITATE: Interogarea SQL este construita prin concatenare directa
 * a input-ului utilizatorului, fara prepared statements.
 * 
 * EXPLOIT: 
 *   - Ocoliti autentificarea: username = admin' -- 
 *   - Extrageti toate parolele: ' OR '1'='1
 */
$page_title = 'SQL Injection Demo';
require_once dirname(__DIR__) . '/includes/header.php';

$result_msg = '';
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    $search = $_GET['search']; // NESANITIZAT - VULNERABIL
    
    // VULNERABIL: concatenare directa in query SQL
    $db = getMySQLPDO() ?: getSQLitePDO();
    if ($db) {
        $query = "SELECT id, username, email, role FROM users WHERE username LIKE '%" . $search . "%' OR email LIKE '%" . $search . "%'";
        try {
            $result = $db->query($query);
            if ($result) {
                $search_results = $result->fetchAll(PDO::FETCH_ASSOC);
                $result_msg = "Query executat: <code>" . htmlspecialchars($query) . "</code>";
            }
        } catch (Exception $e) {
            $result_msg = "Eroare SQL: " . htmlspecialchars($e->getMessage());
        }
    }
}

// VULNERABIL: Autentificare fara prepared statements
$login_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vuln_login'])) {
    $username = $_POST['vuln_username'];
    $password = $_POST['vuln_password'];
    
    $db = getMySQLPDO() ?: getSQLitePDO();
    if ($db) {
        // VULNERABIL: SQL Injection prin concatenare
        $query = "SELECT id, username, role FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'";
        try {
            $result = $db->query($query);
            $user = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
            
            if ($user) {
                $login_result = "Autentificare reusita ca <strong>" . htmlspecialchars($user['username']) . "</strong> (rol: " . htmlspecialchars($user['role'] ?? 'utilizator') . "). Query: <code>" . htmlspecialchars($query) . "</code>";
            } else {
                $login_result = "Autentificare esuata. Query: <code>" . htmlspecialchars($query) . "</code>";
            }
        } catch (Exception $e) {
            $login_result = "Eroare SQL: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<h2>SQL Injection - Demonstratie Vulnerabilitate</h2>

<div style="background: #ffe6e6; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 20px;">
    <strong>VULNERABILITATE ACTIVA:</strong> Aceasta pagina este intentionat vulnerabila la SQL Injection.
    Input-ul utilizatorului este concatenat direct in interogarile SQL, fara sanitizare.
</div>

<section>
    <h2>1. Cautare Utilizatori (Vulnerabil)</h2>
    <p><strong>Exploit sugerat:</strong> Incercati <code>' OR '1'='1</code> sau <code>admin' -- </code> in campul de cautare.</p>
    
    <form method="get" action="sql_injection.php">
        <p>
            <label for="search">Cauta utilizator:</label>
            <input type="text" id="search" name="search" value="<?php echo $_GET['search'] ?? ''; ?>" style="width: 300px;" placeholder="Ex: admin' OR '1'='1">
            <input type="submit" value="Cauta">
        </p>
    </form>
    
    <?php if ($result_msg): ?>
        <p style="background: #f0f0f0; padding: 10px; font-family: monospace; word-break: break-all;">
            <?php echo $result_msg; ?>
        </p>
    <?php endif; ?>
    
    <?php if (!empty($search_results)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Rol</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section style="margin-top: 30px;">
    <h2>2. Autentificare Vulnerabila</h2>
    <p><strong>Exploit sugerat:</strong> Username: <code>admin' -- </code>, Password: <code>orice</code> (comentariul SQL ignora verificarea parolei)</p>
    
    <form method="post" action="sql_injection.php">
        <p>
            <label for="vuln_username">Username:</label>
            <input type="text" id="vuln_username" name="vuln_username" required placeholder="admin' -- ">
        </p>
        <p>
            <label for="vuln_password">Password:</label>
            <input type="password" id="vuln_password" name="vuln_password" placeholder="orice">
        </p>
        <input type="hidden" name="vuln_login" value="1">
        <input type="submit" value="Login (Vulnerabil)">
    </form>
    
    <?php if ($login_result): ?>
        <p style="background: #f0f0f0; padding: 10px; margin-top: 10px;">
            <?php echo $login_result; ?>
        </p>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
