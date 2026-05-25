<?php
/**
 * Path Traversal Attack - Pagina Vulnerabila (Laborator Securitate)
 * 
 * VULNERABILITATE: Parametrul 'file' din URL este folosit direct pentru a citi
 * fisiere de pe server, fara validarea caii. Un atacator poate folosi '../'
 * pentru a naviga in afara directorului permis.
 * 
 * EXPLOIT:
 *   - ?file=../../../../etc/passwd (Linux)
 *   - ?file=../../../includes/config.php (fisier de configurare)
 *   - ?file=../../../../var/log/apache2/access.log (loguri)
 */
$page_title = 'Path Traversal Demo';
require_once dirname(__DIR__) . '/includes/header.php';

$file_content = '';
$file_path = '';
$error_msg = '';

// Directorul "permis" pentru citire fisiere
$allowed_dir = dirname(__DIR__) . '/uploads/';

if (isset($_GET['file'])) {
    // VULNERABIL: calea este construita direct din input-ul utilizatorului
    $requested_file = $_GET['file'];
    
    // VULNERABIL: doar concatenare simpla, fara validare reala
    $file_path = $allowed_dir . $requested_file;
    
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
    } else {
        $error_msg = 'Fisierul nu exista: ' . $file_path;
    }
}

// VULNERABIL: Listare fisiere in directorul "permis" - arata structura
$files_in_dir = [];
if (is_dir($allowed_dir)) {
    $files_in_dir = array_diff(scandir($allowed_dir), ['.', '..']);
}
?>

<h2>Path Traversal Attack - Demonstratie Vulnerabilitate</h2>

<div style="background: #ffe6e6; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 20px;">
    <strong>VULNERABILITATE ACTIVA:</strong> Aceasta pagina permite citirea de fisiere arbitrare
    de pe server folosind secventa <code>../</code> in parametrul 'file'.
    Nu exista validare a caii reale a fisierului.
</div>

<section>
    <h2>Vizualizare Fisiere (Vulnerabil la Path Traversal)</h2>
    
    <p><strong>Director permis (teoretic):</strong> <code><?php echo htmlspecialchars($allowed_dir); ?></code></p>
    
    <h3>Exploit-uri sugerate:</h3>
    <ul>
        <li><a href="path_traversal.php?file=../../includes/config.php">Citeste config.php (parole DB!)</a></li>
        <li><a href="path_traversal.php?file=../../../../etc/passwd">Citeste /etc/passwd</a></li>
        <li><a href="path_traversal.php?file=../../../../etc/hostname">Citeste hostname server</a></li>
        <li><a href="path_traversal.php?file=../../../zzz/IMPLEMENTARE.md">Citeste documentatia proiectului</a></li>
    </ul>
    
    <form method="get" action="path_traversal.php">
        <p>
            <label for="file">Cale fisier (relativ la directorul uploads):</label>
            <input type="text" id="file" name="file" value="<?php echo htmlspecialchars($_GET['file'] ?? ''); ?>" 
                   style="width: 500px;" placeholder="../../includes/config.php">
            <input type="submit" value="Citeste Fisier">
        </p>
    </form>
    
    <?php if ($error_msg): ?>
        <p style="color: red; background: #fdecea; padding: 10px;"><?php echo htmlspecialchars($error_msg); ?></p>
    <?php endif; ?>
    
    <?php if ($file_path): ?>
        <div style="background: #f0f0f0; padding: 10px; margin-top: 10px; font-family: monospace;">
            <strong>Cale rezolvata:</strong> <?php echo htmlspecialchars($file_path); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($file_content !== ''): ?>
        <h3>Continut Fisier:</h3>
        <pre style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px;"><?php echo htmlspecialchars($file_content); ?></pre>
    <?php endif; ?>
</section>

<section style="margin-top: 30px;">
    <h2>Fisiere in Directorul "Permis"</h2>
    <p>Acestea sunt fisierele vizibile in directorul uploads (fara path traversal):</p>
    
    <?php if (empty($files_in_dir)): ?>
        <p>Directorul este gol.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($files_in_dir as $f): ?>
                <li>
                    <a href="path_traversal.php?file=<?php echo urlencode($f); ?>">
                        <?php echo htmlspecialchars($f); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
