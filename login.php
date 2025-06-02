<?php
header("Access-Control-Allow-Origin: *");  // Adjust this for production
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Read JSON input
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Missing name or password."]);
    exit;
}

// DB credentials from environment
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "5432";
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
    $stmt->execute([":name" => $data->name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Replace with password_verify if using hashed passwords
    if ($user && $data->password === $user['password']) {
        // Set cookie for 1 day, readable by JS, secure if HTTPS, SameSite=Lax
        setcookie("username", $user['name'], [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => true,      // set false if testing locally without HTTPS
            'httponly' => false,   // allow JS access to cookie
            'samesite' => 'Lax'
        ]);

        echo json_encode(["success" => true, "username" => $user['name']]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error."]);
}
