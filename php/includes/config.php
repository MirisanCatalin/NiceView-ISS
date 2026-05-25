<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'niceview_user');
define('DB_PASS', 'secure_password_123');
define('DB_NAME', 'niceview_db');

define('DB_SQLITE_PATH', dirname(__DIR__) . '/database/niceview.sqlite');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

$mysql_pdo = null;
$sqlite_pdo = null;

// Define MYSQLI_ASSOC fallback for when MySQLi extension is not available
if (!defined('MYSQLI_ASSOC')) {
    define('MYSQLI_ASSOC', 1);
}
if (!defined('MYSQLI_NUM')) {
    define('MYSQLI_NUM', 2);
}
if (!defined('MYSQLI_BOTH')) {
    define('MYSQLI_BOTH', 3);
}

/**
 * SQLite wrapper that mimics MySQLi interface for compatibility.
 */
class SQLiteMySQLiCompat {
    private $pdo;
    public $error = '';
    public $connect_error = '';
    public $errno = 0;
    public $insert_id = 0;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function set_charset($charset) {
        // SQLite doesn't need charset setting
        return true;
    }

    public function close() {
        $this->pdo = null;
    }

    /**
     * Execute a raw SQL query and return a result-like object.
     */
    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            if ($stmt === false) {
                $this->error = 'Query failed';
                return false;
            }
            // Track insert_id for INSERT queries
            if (stripos(trim($sql), 'INSERT') === 0) {
                $this->insert_id = (int)$this->pdo->lastInsertId();
            }
            return new SQLiteResultCompat($stmt);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Prepare a statement. Returns a stmt-like object.
     */
    public function prepare($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                $this->error = 'Prepare failed';
                return false;
            }
            return new SQLiteStmtCompat($stmt, $this);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}

class SQLiteResultCompat {
    private $stmt;
    private $fetched_all = null;
    private $position = 0;
    public $num_rows = 0;

    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
        $this->fetched_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->position = 0;
        $this->num_rows = count($this->fetched_all);
    }

    public function fetch_assoc() {
        if ($this->position < count($this->fetched_all)) {
            return $this->fetched_all[$this->position++];
        }
        return null;
    }

    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->fetched_all;
    }

    public function fetchColumn($column = 0) {
        $row = $this->fetch_assoc();
        if ($row) {
            $values = array_values($row);
            return $values[$column] ?? null;
        }
        return false;
    }

    public function get_result() {
        return $this;
    }
}

class SQLiteStmtCompat {
    private $stmt;
    private $params = [];
    private $parent;
    public $affected_rows = 0;

    public function __construct(PDOStatement $stmt, $parent = null) {
        $this->stmt = $stmt;
        $this->parent = $parent;
    }

    public function bind_param($types, &...$params) {
        $this->params = [];
        foreach ($params as $i => $val) {
            $this->params[$i + 1] = $val;
        }
        return true;
    }

    public function execute($params = null) {
        try {
            if ($params !== null) {
                $result = $this->stmt->execute($params);
            } else {
                $result = $this->stmt->execute(array_values($this->params));
            }
            if ($result) {
                $this->affected_rows = $this->stmt->rowCount();
                if ($this->parent && stripos($this->stmt->queryString, 'INSERT') === 0) {
                    $this->parent->insert_id = (int)$this->parent->pdo->lastInsertId();
                }
            }
            return $result;
        } catch (PDOException $e) {
            $this->parent->error = $e->getMessage();
            return false;
        }
    }

    public function get_result() {
        return new SQLiteResultCompat($this->stmt);
    }

    public function fetch() {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function close() {
        $this->stmt->closeCursor();
    }
}

/**
 * Get a database connection - uses SQLite when MySQL is unavailable.
 * Returns an object compatible with MySQLi (has prepare, query, error).
 */
function getMySQLiConnection() {
    static $sqlite_fallback = null;
    if ($sqlite_fallback !== null) {
        return $sqlite_fallback;
    }

    // Try MySQL - suppress mysqli exception and handle manually
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn && !$conn->connect_error) {
        $conn->set_charset("utf8mb4");
        return $conn;
    }

    // Fallback to SQLite
    $pdo = getSQLitePDO_raw();
    if ($pdo !== null) {
        $sqlite_fallback = new SQLiteMySQLiCompat($pdo);
        return $sqlite_fallback;
    }

    return null;
}

function getMySQLPDO() {
    // Try MySQL first
    global $mysql_pdo;
    if ($mysql_pdo !== null) {
        return $mysql_pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $mysql_pdo = new PDO($dsn, DB_USER, DB_PASS);
        $mysql_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $mysql_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $mysql_pdo;
    } catch (PDOException $e) {
        // Fallback: use SQLite PDO
        return getSQLitePDO_raw();
    }
}

/**
 * Get raw SQLite PDO without the user/settings creation logic.
 */
function getSQLitePDO_raw() {
    global $sqlite_pdo;
    if ($sqlite_pdo !== null) {
        return $sqlite_pdo;
    }
    try {
        if (!file_exists(DB_SQLITE_PATH)) {
            return null;
        }
        $sqlite_pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
        $sqlite_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlite_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Enable WAL mode for better concurrent access
        $sqlite_pdo->exec("PRAGMA journal_mode=WAL");
        // Enable foreign keys
        $sqlite_pdo->exec("PRAGMA foreign_keys=ON");
        return $sqlite_pdo;
    } catch (PDOException $e) {
        error_log("SQLite Error: " . $e->getMessage());
        return null;
    }
}

function getSQLitePDO() {
    global $sqlite_pdo;
    if ($sqlite_pdo === null) {
        try {
            $db_path = dirname(DB_SQLITE_PATH);
            if (!is_dir($db_path)) {
                mkdir($db_path, 0755, true);
            }
            if (!file_exists(DB_SQLITE_PATH)) {
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
                $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    email TEXT,
                    role TEXT DEFAULT 'utilizator'
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS privelisti (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    titlu VARCHAR(100) NOT NULL,
                    descriere TEXT,
                    judet VARCHAR(50),
                    localitate VARCHAR(50),
                    tip VARCHAR(10) DEFAULT 'munte',
                    altitudine INTEGER,
                    lat REAL,
                    lng REAL,
                    website VARCHAR(255),
                    status VARCHAR(20) DEFAULT 'in_asteptare',
                    user_id INTEGER,
                    imagine VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                )");
                $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES 
                    ('site_name', 'NiceView'),
                    ('site_description', 'Platforma pentru gestionarea priveliștilor turistice')");
                
                $pass = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // 'password'
                $pdo->exec("INSERT OR IGNORE INTO users (username, password, email, role) VALUES 
                    ('admin', '$pass', 'admin@niceview.ro', 'administrator'),
                    ('user1', '$pass', 'user1@niceview.ro', 'utilizator')");
            }
            $sqlite_pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            $sqlite_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlite_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $sqlite_pdo->exec("PRAGMA journal_mode=WAL");
            $sqlite_pdo->exec("PRAGMA foreign_keys=ON");
        } catch (PDOException $e) {
            error_log("SQLite Error: " . $e->getMessage());
            return null;
        }
    }
    return $sqlite_pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'administrator';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        $current_dir = basename(dirname($_SERVER['PHP_SELF']));
        $prefix = ($current_dir === 'php') ? 'auth/' : ($current_dir === 'auth' ? '' : '../auth/');
        header('Location: ' . $prefix . 'login.php');
        exit;
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        $current_dir = basename(dirname($_SERVER['PHP_SELF']));
        $prefix = ($current_dir === 'php') ? '' : '../';
        header('Location: ' . $prefix . 'index.php');
        exit;
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function setRememberMeCookie($user_id, $token) {
    $expire = time() + (30 * 24 * 60 * 60);
    setcookie('remember_token', $token, $expire, '/', '', false, true);
    setcookie('remember_user', $user_id, $expire, '/', '', false, true);
}

function clearRememberMeCookie() {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    setcookie('remember_user', '', time() - 3600, '/', '', false, true);
}
