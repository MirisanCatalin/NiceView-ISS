<?php
/**
 * NiceView - Pagina Index Vulnerabilitati (Laborator Securitate Web)
 * 
 * ATENTIE: Aceasta pagina face parte din demonstratia de securitate web.
 * Contine INTENTIONAT vulnerabilitati pentru scopuri educationale.
 * Dupa demonstrarea exploit-urilor, aceste vulnerabilitati vor fi remediate.
 */
$page_title = 'Laborator Securitate Web';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<section>
    <h2>Vulnerabilitati Implementate</h2>
    
    <div class="cards">
        <a href="sql_injection.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>SQL Injection</h3>
            <p>Interogare vulnerabila cu concatenare directa a input-ului utilizatorului</p>
            <small>Exploit: Ocoliti autentificarea sau extrageti date din baza de date</small>
        </a>
        
        <a href="xss.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Cross-Site Scripting (XSS)</h3>
            <p>Reflectare input fara sanitizare in output HTML</p>
            <small>Exploit: Injectati JavaScript malitios in pagina</small>
        </a>
        
        <a href="csrf_vulnerable.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Cross-Site Request Forgery (CSRF)</h3>
            <p>Formular fara token CSRF, susceptibil la atacuri cross-site</p>
            <small>Exploit: Trimiteti o cerere neautorizata de pe un alt site</small>
        </a>
        
        <a href="file_upload.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Unrestricted File Upload</h3>
            <p>Upload fisiere fara validare de tip sau continut</p>
            <small>Exploit: Incarcati un shell PHP pe server</small>
        </a>
        
        <a href="path_traversal.php" class="card" style="text-decoration: none; color: inherit;">
            <h3>Path Traversal</h3>
            <p>Citire fisiere fara validarea caii pe sistemul de fisiere</p>
            <small>Exploit: Cititi fisiere arbitrare de pe server</small>
        </a>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
