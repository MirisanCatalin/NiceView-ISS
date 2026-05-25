<?php
require_once __DIR__ . '/config.php';

function generateCaptcha() {
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= rand(0, 9);
    }
    $_SESSION['captcha_code'] = $code;
    
    $width = 120;
    $height = 40;
    $im = imagecreatetruecolor($width, $height);
    
    $bg = imagecolorallocate($im, 255, 255, 255);
    $text_color = imagecolorallocate($im, 0, 0, 0);
    $noise_color = imagecolorallocate($im, 100, 120, 180);
    
    imagefill($im, 0, 0, $bg);
    
    for ($i = 0; $i < 10; $i++) {
        imageline($im, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
    }
    
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($code);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($im, $font_size, $x, $y, $code, $text_color);
    
    header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);
}

function verifyCaptcha($code) {
    if (!isset($_SESSION['captcha_code'])) {
        return false;
    }
    return strcasecmp($_SESSION['captcha_code'], $code) === 0;
}

/**
 * Ensure remember_tokens table exists (for SQLite fallback mode).
 */
function ensureRememberTokensTable($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_remember_token ON remember_tokens(token)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_remember_expires ON remember_tokens(expires_at)");
    } catch (Exception $e) {
        // Table might already exist in MySQL mode - ignore
    }
}

function login($username, $password, $remember = false) {
    $mysqli = getMySQLiConnection();
    if ($mysqli === null) {
        return false;
    }
    
    $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if ($stmt === false) {
        return false;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['login_time'] = time();
            
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $user_id = $row['id'];
                
                $pdo = getMySQLPDO();
                if ($pdo !== null) {
                    ensureRememberTokensTable($pdo);
                    try {
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$user_id, $token, $expires]);
                    } catch (Exception $e) {
                        // If MySQL mode, try original syntax
                        try {
                            $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
                            $stmt->execute([$user_id, $token]);
                        } catch (Exception $e2) {
                            // Silently fail remember-me
                        }
                    }
                    setRememberMeCookie($user_id, $token);
                }
            }
            
            return true;
        }
    }
    
    return false;
}

function logout() {
    if (isset($_SESSION['user_id'])) {
        $pdo = getMySQLPDO();
        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } catch (Exception $e) {
                // Table may not exist
            }
        }
    }
    
    session_unset();
    session_destroy();
    clearRememberMeCookie();
}

function checkRememberMe() {
    if (isLoggedIn()) {
        return true;
    }
    
    if (isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
        $token = $_COOKIE['remember_token'];
        $user_id = (int)$_COOKIE['remember_user'];
        
        $pdo = getMySQLPDO();
        if ($pdo === null) {
            return false;
        }
        
        ensureRememberTokensTable($pdo);
        
        $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE user_id = ? AND token = ? AND expires_at > datetime('now')");
        $stmt->execute([$user_id, $token]);
        
        if ($row = $stmt->fetch()) {
            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                return true;
            }
        } else {
            clearRememberMeCookie();
        }
    }
    return false;
}
?>