<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "5432";
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read JSON input
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->password)) {
        echo json_encode(['error' => 'Missing name or password']);
        exit;
    }

    $name = $data->name;
    $password = $data->password;

    $stmt = $conn->prepare("SELECT total_levied FROM users WHERE name = :name AND password = :password");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['totalLevied' => $result['total_levied'] ?? 0]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
