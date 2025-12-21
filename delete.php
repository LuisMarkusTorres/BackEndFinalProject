<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

$db = new Database();

// Get the anonymous code from URL
$code = $_GET['code'] ?? '';

// Verify code and get real ID
if (!isset($_SESSION['experience_codes'][$code])) {
    header("Location: statistics.php?error=Invalid+code");
    exit();
}

$id = $_SESSION['experience_codes'][$code];

$experience = new DrivingExperience($db);
$experience->id = $id;

$result = $experience->delete();

if ($result['success']) {
    // Remove from session codes
    unset($_SESSION['experience_codes'][$code]);
    
    header("Location: statistics.php?deleted=1");
    exit();
} else {
    $errorMsg = implode(", ", $result['errors']);
    header("Location: statistics.php?error=" . urlencode($errorMsg));
    exit();
}
?>