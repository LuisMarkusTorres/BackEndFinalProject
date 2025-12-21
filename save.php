<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new Database();
    $experience = new DrivingExperience($db);
    
    // Set properties from POST data
    $experience->entryDate = $_POST["drivingDateInput"];
    $experience->startTime = $_POST["startTimeInput"];
    $experience->endTime = $_POST["endTimeInput"];
    $experience->distanceKm = $_POST["distanceInput"];
    $experience->weatherId = $_POST["weatherInput"];
    $experience->trafficId = $_POST["trafficInput"];
    $experience->slipperinessId = $_POST["slipperinessInput"];
    $experience->lightId = $_POST["lightInput"];
    
    // Process maneuvers
    $maneuvers = $_POST["maneuvers"] ?? [];
    $quantities = $_POST["quantities"] ?? [];
    
    for ($i = 0; $i < count($maneuvers); $i++) {
        $name = trim($maneuvers[$i]);
        $qty = intval($quantities[$i]);
        
        if ($name !== "" && $qty > 0) {
            $experience->maneuvers[] = [
                'name' => $name,
                'quantity' => $qty
            ];
        }
    }
    
    // Save the experience
    $result = $experience->save();
    
    if ($result['success']) {
        // Generate anonymous code for this experience
        $code = bin2hex(random_bytes(8));
        $_SESSION['experience_codes'][$code] = $result['id'];
        
        header("Location: index.php?success=1");
        exit();
    } else {
        // Handle errors
        $errorMsg = implode(", ", $result['errors']);
        header("Location: index.php?error=" . urlencode($errorMsg));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>