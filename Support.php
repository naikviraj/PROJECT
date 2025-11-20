<?php
require "conn.php"; // your PDO connection file

$db = getPDO(); // get PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect form values safely
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    // Insert query
    $sql = "INSERT INTO support (name, email, message)
            VALUES (:name, :email, :message)";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':message' => $message
    ]);

    echo "Message submitted successfully!";
}
?>

