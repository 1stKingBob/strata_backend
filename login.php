<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Missing name or password."]);
    exit;
}

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

    if ($user && password_verify($data->password, $user['password'])) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
