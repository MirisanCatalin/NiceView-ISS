<?php
require_once dirname(__DIR__) . '/includes/config.php';

$mysqli = getMySQLiConnection();
if ($mysqli === null) {
    die("Error: Could not connect to MySQL database.\n");
}

$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // 'password'

echo "Populating users...\n";

for ($i = 2; $i <= 20; $i++) {
    $username = "user" . $i;
    $email = $username . "@niceview.ro";
    $role = "utilizator";
    
    $stmt = $mysqli->prepare("INSERT IGNORE INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password_hash, $email, $role);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Created $username\n";
        } else {
            echo "$username already exists\n";
        }
    } else {
        echo "Error creating $username: " . $stmt->error . "\n";
    }
}

echo "Done!\n";
?>