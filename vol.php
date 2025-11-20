<?php
require "conn.php";

$db = getPDO();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Basic form fields
    $name       = $_POST['name'] ?? '';
    $email      = $_POST['email'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $address    = $_POST['address'] ?? '';
    $hours      = $_POST['hours'] ?? '';
    $motivation = $_POST['motivation'] ?? '';

    // Checkbox arrays — convert to comma-separated strings
    $days    = isset($_POST['days']) ? implode(", ", $_POST['days']) : '';
    $comfort = isset($_POST['comfort']) ? implode(", ", $_POST['comfort']) : '';
    $areas   = isset($_POST['areas']) ? implode(", ", $_POST['areas']) : '';

    // Insert data
    $sql = "INSERT INTO volunteering 
            (name, email, phone, address, days, hours, comfort, areas, motivation)
            VALUES (:name, :email, :phone, :address, :days, :hours, :comfort, :areas, :motivation)";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':name'       => $name,
        ':email'      => $email,
        ':phone'      => $phone,
        ':address'    => $address,
        ':days'       => $days,
        ':hours'      => $hours,
        ':comfort'    => $comfort,
        ':areas'      => $areas,
        ':motivation' => $motivation
    ]);

    echo "Your volunteering form has been submitted successfully!";
}
?>
