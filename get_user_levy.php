<?php
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "5432";
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Assume name and password are passed via POST
$name = $_POST['name'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT total_levied FROM users WHERE name = :name AND password = :password");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':password', $password);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['totalLevied' => $result['total_levied'] ?? 0]);
?>
