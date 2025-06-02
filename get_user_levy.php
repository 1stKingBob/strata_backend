<?php
// ✅ Allow requests from your frontend (no trailing slash!)
header("Access-Control-Allow-Origin: https://strata-savvy-solutions.vercel.app");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
// ✅ Only respond to POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

try {
    // ✅ Load database credentials from environment
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT") ?: "5432";
    $dbname = getenv("DB_NAME");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS");

    // ✅ Connect to PostgreSQL database
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Decode incoming JSON data
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing name or password']);
        exit;
    }

    $name = $data->name;
    $password = $data->password;

    // ✅ Prepare and run query
    $stmt = $conn->prepare("SELECT total_levied FROM users WHERE name = :name AND password = :password");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found or wrong credentials']);
        exit;
    }

    // ✅ Return result as JSON
    echo json_encode(['totalLevied' => $result['total_levied']]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
