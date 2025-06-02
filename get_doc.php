<?php
// ✅ CORS settings (adjust origin to your actual frontend domain)
header("Access-Control-Allow-Origin: https://stratamanagementweb-five.vercel.app");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// ✅ Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

try {
    // ✅ Load DB credentials from environment
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT") ?: "5432";
    $dbname = getenv("DB_NAME");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS");

    // ✅ Connect to PostgreSQL using PDO
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Parse JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["name"]) || !isset($input["password"])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing name or password"]);
        exit;
    }

    $name = $input["name"];
    $password = $input["password"];

    // ✅ Query for document stats
    $stmt = $conn->prepare("SELECT total_doc, public_access FROM users WHERE name = :name AND password = :password");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":password", $password);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(["error" => "User not found or wrong credentials"]);
        exit;
    }

    echo json_encode([
        "totalDoc" => (int)$row["total_doc"],
        "publicAccess" => (int)$row["public_access"]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
