<?php
$page_title = 'Contact';
require_once __DIR__ . '/includes/header.php';
?>

<h2>Contact</h2>

<p>Pentru orice întrebări sau sugestii, vă rugăm să ne contactați folosind formularul de mai jos.</p>

<form action="#" method="post">
    <fieldset>
        <legend><strong>Trimite mesaj</strong></legend>
        <p>
            <label for="nume">Nume:</label>
            <input type="text" id="nume" name="nume" required value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
        </p>
        <p>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </p>
        <p>
            <label for="subiect">Subiect:</label>
            <select id="subiect" name="subiect">
                <option value="suport">Suport Tehnic</option>
                <option value="sugestie">Sugestie Priveliște</option>
                <option value="altele">Altele</option>
            </select>
        </p>
        <p>
            <label for="mesaj">Mesaj:</label>
            <textarea id="mesaj" name="mesaj" rows="6" required placeholder="Scrie aici mesajul tău..."></textarea>
        </p>
    </fieldset>
    <div class="form-buttons">
        <input type="submit" value="Trimite Mesajul">
        <input type="reset" value="Resetează">
    </div>
</form>

<section style="margin-top: 40px;">
    <h2>Alte modalități de contact</h2>
    <p><strong>Email:</strong> contact@niceview.ro</p>
    <p><strong>Telefon:</strong> +40 700 000 000</p>
    <p><strong>Adresă:</strong> Str. Universității Nr. 1, Cluj-Napoca, România</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>