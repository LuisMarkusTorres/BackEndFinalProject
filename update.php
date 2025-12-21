<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new Database();
    
    // Get the anonymous code
    $code = $_POST['code'] ?? '';
    
    // Verify code and get real ID
    if (!isset($_SESSION['experience_codes'][$code])) {
        header("Location: statistics.php?error=Invalid+code");
        exit();
    }
    
    $id = $_SESSION['experience_codes'][$code];
    
    $experience = new DrivingExperience($db);
    $experience->loadById($id);
    
    // Update properties from POST data
    $experience->entryDate = $_POST["drivingDateInput"];
    $experience->startTime = $_POST["startTimeInput"];
    $experience->endTime = $_POST["endTimeInput"];
    $experience->distanceKm = $_POST["distanceInput"];
    $experience->weatherId = $_POST["weatherInput"];
    $experience->trafficId = $_POST["trafficInput"];
    $experience->slipperinessId = $_POST["slipperinessInput"];
    $experience->lightId = $_POST["lightInput"];
    
    // Process maneuvers
    $experience->maneuvers = [];
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
    
    // Update the experience
    $result = $experience->update();
    
    if ($result['success']) {
        header("Location: statistics.php?updated=1");
        exit();
    } else {
        $errorMsg = implode(", ", $result['errors']);
        header("Location: edit.php?code=$code&error=" . urlencode($errorMsg));
        exit();
    }
} else {
    header("Location: statistics.php");
    exit();
}
?>