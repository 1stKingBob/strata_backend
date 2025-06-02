<?php
$host = "your-db-host";
$dbname = "strata_cnw3";
$user = "your-db-user";
$pass = "your-db-pass";

$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);

$query = "SELECT SUM(total_levied) AS total_levied FROM users;";
$stmt = $conn->prepare($query);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(["totalLevied" => $result["total_levied"] ?? 0]);
?>
