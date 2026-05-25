<?php
$page_title = 'Adaugă Priveliște';
require_once dirname(__DIR__) . '/includes/header.php';

checkRememberMe();
redirectIfNotLoggedIn();

$pdo = getMySQLPDO();
$mysqli = getMySQLiConnection();

$error = '';
$success = '';

// Pre-fill logic (Requirement 7)
$user_email = '';
if ($mysqli) {
    $stmt = $mysqli->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_email = $stmt->get_result()->fetch_assoc()['email'] ?? '';
}

$last_p = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM privelisti WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $last_p = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalid. Vă rugăm reîncărcați pagina.';
    } else {
        $titlu = trim($_POST['titlu'] ?? '');
        $descriere = trim($_POST['descriere'] ?? '');
        $judet = $_POST['judet'] ?? '';
        $localitate = $_POST['localitate'] ?? '';
        $tip = $_POST['tip'] ?? 'munte';
        $altitudine = (int)($_POST['altitudine'] ?? 0);
        $lat = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
        $website = trim($_POST['website'] ?? '');
        
        if (empty($titlu)) {
            $error = 'Titlul este obligatoriu.';
        } else {
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO privelisti (titlu, descriere, judet, localitate, tip, altitudine, lat, lng, website, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_asteptare')");
                if ($stmt->execute([$titlu, $descriere, $judet, $localitate, $tip, $altitudine, $lat, $lng, $website, $_SESSION['user_id']])) {
                    $success = 'Priveliștea a fost adăugată și va fi revizuită de un administrator.';
                } else {
                    $error = 'Eroare la salvarea în baza de date.';
                }
            }
        }
    }
}
?>

<h2>Adaugă o priveliște</h2>

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

<form action="adauga.php" method="post" name="formular_priveliste">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

    <fieldset>
        <legend><strong>Date generale (Precompletate)</strong></legend>

        <p>
            <label for="nume">Nume utilizator:</label>
            <input type="text" id="nume" name="nume" maxlength="30" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
        </p>

        <p>
            <label for="email">Email contact:</label>
            <input type="text" id="email" name="email" maxlength="40" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
        </p>

        <p>
            <label for="titlu">Titlu priveliște:</label>
            <input type="text" id="titlu" name="titlu" maxlength="50" required value="<?php echo htmlspecialchars($last_p['titlu'] ?? ''); ?>">
        </p>

        <p>
            <label for="judet">Județ:</label>
            <select id="judet" name="judet">
                <option value="">-- Selectați județul --</option>
                <?php foreach (['AB','AR','AG','BC','BH','BN','BT','BV','BR','BZ','CS','CL','CJ','CT','CV','DB','DJ','GL','GR','GJ','HR','HD','IL','IS','IF','MM','MH','MS','NT','OT','PH','SM','SJ','SB','SV','TR','TM','TL','VS','VL','VN','B'] as $code): ?>
                    <option value="<?php echo $code; ?>" <?php echo (($last_p['judet'] ?? '') === $code) ? 'selected' : ''; ?>><?php echo $code; ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="localitate">Localitate:</label>
            <input type="text" id="localitate" name="localitate" value="<?php echo htmlspecialchars($last_p['localitate'] ?? ''); ?>">
        </p>
    </fieldset>

    <fieldset>
        <legend><strong>Tip priveliște</strong></legend>
        <p>
            <label for="tip">Tip peisaj:</label>
            <select id="tip" name="tip">
                <option value="munte" <?php echo (($last_p['tip'] ?? '') === 'munte') ? 'selected' : ''; ?>>Munte</option>
                <option value="mare" <?php echo (($last_p['tip'] ?? '') === 'mare') ? 'selected' : ''; ?>>Mare</option>
                <option value="lac" <?php echo (($last_p['tip'] ?? '') === 'lac') ? 'selected' : ''; ?>>Lac</option>
                <option value="oras" <?php echo (($last_p['tip'] ?? '') === 'oras') ? 'selected' : ''; ?>>Oraș</option>
            </select>
        </p>
    </fieldset>

    <fieldset>
        <legend><strong>Detalii suplimentare</strong></legend>

        <p>
            <label for="altitudine">Altitudine (max 3000 m):</label>
            <input type="number" id="altitudine" name="altitudine" min="0" max="3000" step="1" value="<?php echo htmlspecialchars($last_p['altitudine'] ?? ''); ?>">
        </p>

        <p>
            <label for="lat">Latitudine (ex: 45.9432):</label>
            <input type="number" id="lat" name="lat" step="0.000001" min="-90" max="90" value="<?php echo htmlspecialchars($last_p['lat'] ?? ''); ?>">
        </p>

        <p>
            <label for="lng">Longitudine (ex: 24.9668):</label>
            <input type="number" id="lng" name="lng" step="0.000001" min="-180" max="180" value="<?php echo htmlspecialchars($last_p['lng'] ?? ''); ?>">
        </p>

        <p>
            <label for="descriere">Descriere:</label>
            <textarea id="descriere" name="descriere" cols="40" rows="5"><?php echo htmlspecialchars($last_p['descriere'] ?? ''); ?></textarea>
        </p>

        <p>
            <label for="website">Website (opțional):</label>
            <input type="text" id="website" name="website" placeholder="https://..." value="<?php echo htmlspecialchars($last_p['website'] ?? ''); ?>">
        </p>

        <p>
            <label><input type="checkbox" name="regulament" value="da" required> Sunt de acord cu regulamentul</label>
        </p>
    </fieldset>

    <div class="form-buttons">
        <input type="submit" value="Trimite spre aprobare">
        <input type="reset" value="Resetează">
    </div>

</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>