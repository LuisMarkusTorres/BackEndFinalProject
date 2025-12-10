<?php

$DB_SERVER = "mysql-luismarkustorres.alwaysdata.net";
$DB_USERNAME  = "443649";
$DB_PASSWORD  = "3#Pb*qDYLj3xV6]";
$DB_NAME      = "luismarkustorres_pw3db";

try {
    $pdo = new PDO(
        "mysql:host=$DB_SERVER;dbname=$DB_NAME;charset=utf8",
        $DB_USERNAME,
        $DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $date         = $_POST["drivingDateInput"];
    $start        = $_POST["startTimeInput"];
    $end          = $_POST["endTimeInput"];
    $distance     = $_POST["distanceInput"];
    $weather      = $_POST["weatherInput"];
    $traffic      = $_POST["trafficInput"];
    $slipperiness = $_POST["slipperinessInput"];
    $light        = $_POST["lightInput"];

    try {
        $sql = "INSERT INTO driving_experiences
                (driving_date, start_time, end_time, distance_km, weather_id, traffic_id, slipperiness_id, light_id)
                VALUES (:date, :start, :end, :distance, :weather, :traffic, :slipperiness, :light)";
        
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(":date",         $date);
        $stmt->bindValue(":start",        $start);
        $stmt->bindValue(":end",          $end);
        $stmt->bindValue(":distance",     $distance);
        $stmt->bindValue(":weather",      $weather);
        $stmt->bindValue(":traffic",      $traffic);
        $stmt->bindValue(":slipperiness", $slipperiness);
        $stmt->bindValue(":light",        $light);

        $stmt->execute();

        header("Location: index.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Insert error: " . $e->getMessage());
    }
}

$maneuvers  = $_POST["maneuvers"] ?? [];
$quantities = $_POST["quantities"] ?? [];

$filtered_maneuvers = [];

for ($i = 0; $i < count($maneuvers); $i++) {
    $name = trim($maneuvers[$i]);
    $qty  = intval($quantities[$i]);

    if ($name !== "" && $qty > 0) {
        $filtered_maneuvers[] = [
            "maneuver" => $name,
            "qty" => $qty
        ];
    }
}

$maneuvers_json = json_encode($filtered_maneuvers);

?>
