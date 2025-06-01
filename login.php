<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Read and log input
$data = json_decode(file_get_contents("php://input"));
error_log("Received login data: " . json_encode($data));

if (!isset($data->name) || !isset($data->password)) {
    error_log("Missing name or password in request.");
    echo json_encode(["success" => false, "message" => "Missing name or password."]);
    exit;
}

// Get DB credentials from environment variables
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "5432";
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

// Optional: Log DB credentials (for debugging only — remove later)
error_log("DB creds - host: $host, dbname: $dbname, user: $user");

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Prepare and execute statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
    $stmt->execute([":name" => $data->name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("Queried user: " . json_encode($user));

    // Verify password
    if (if ($user && $data->password === $user['password'])) {
        error_log("Login success for user: " . $data->name);
        echo json_encode(["success" => true]);
    } else {
        error_log("Login failed: Invalid credentials for user " . $data->name);
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
} catch (PDOException $e) {
    error_log("PDO error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
