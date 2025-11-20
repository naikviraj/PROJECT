<?php
require "conn.php";

$db = getPDO();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name       = $_POST['name'] ?? '';
    $email      = $_POST['email'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $address    = $_POST['address'] ?? '';
    $hours      = $_POST['hours'] ?? '';
    $motivation = $_POST['motivation'] ?? '';

    $days    = isset($_POST['days']) ? implode(", ", $_POST['days']) : "";
    $comfort = isset($_POST['comfort']) ? implode(", ", $_POST['comfort']) : "";
    $areas   = isset($_POST['areas']) ? implode(", ", $_POST['areas']) : "";

    $sql = "INSERT INTO volunteering
            ($name, $email, $phone, $address, $days, $hours, $comfort, $areas, $motivation)
            VALUES
            (:full_name, :email, :ph_number, :address, :days, :hours_per_week, :comfort_with_dogs, :vol_areas, :why_volunteer)";

    try {
        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':full_name'         => $name,
            ':email'             => $email,
            ':ph_number'         => $phone,
            ':address'           => $address,
            ':days'              => $days,
            ':hours_per_week'    => $hours,
            ':comfort_with_dogs' => $comfort,
            ':vol_areas'         => $areas,
            ':why_volunteer'     => $motivation
        ]);

        echo "Form submitted successfully!";
    } 
    catch (PDOException $e) {
        echo "SQL Error: " . $e->getMessage();
    }
}
?>
