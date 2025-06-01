<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Missing name or password."]);
    exit;
}

$host = "dpg-d0u1u1re5dus738h7bgg-a";
$port = "5432";
$dbname = "strata_cnw3";
$user = "admin";
$pass = "1EDIguNUpCWRRlVSusl5iQ0b3VFDujeQ";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Step 1: Query user by name only
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
    $stmt->execute([":name" => $data->name]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Step 2: Verify password using password_verify
        if (password_verify($data->password, $user['password'])) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
