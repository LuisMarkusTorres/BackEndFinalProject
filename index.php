<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

$db = new Database();

// Fetch dropdown options from lookup tables
$weatherOptions = DrivingExperience::getWeatherConditions($db);
$trafficOptions = DrivingExperience::getTrafficLevels($db);
$slipperinessOptions = DrivingExperience::getRoadConditions($db);
$lightOptions = DrivingExperience::getLightConditions($db);

// Handle success/error messages
$successMessage = isset($_GET['success']) ? 'Driving experience saved successfully!' : '';
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Driving Experience Log</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="main_page.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script type="module" src="final_project.js" defer></script>
</head>
<body>
    <header>
        <h1>Driving Experience Log</h1>
        <hr />
        <p class="distance-info">Track your driving progress and conditions</p>
        <p class="detailed-info">
            <b><a href="statistics.php">View Statistics →</a></b>
        </p>
    </header>

    <?php if ($successMessage): ?>
        <div class="success-message"><?= $successMessage ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="error-message"><?= $errorMessage ?></div>
    <?php endif; ?>

    <main>
        <form action="save.php" method="post">
            <fieldset id="driving-info">
                <legend>Driving experience information</legend>
                <div class="label-input">
                    <label for="drivingDateInput">
                        <span>Driving Experience's Date:</span>
                    </label>
                    <input type="date" id="drivingDateInput" name="drivingDateInput" required />
                </div>
                <div class="label-input">
                    <label for="startTimeInput">
                        <span>Start Time:</span>
                    </label>
                    <input type="time" id="startTimeInput" name="startTimeInput" required />
                </div>
                <div class="label-input">
                    <label for="endTimeInput">
                        <span>End Time:</span>
                    </label>
                    <input type="time" id="endTimeInput" name="endTimeInput" required />
                </div>
                <div class="label-input">
                    <label for="distanceInput">
                        <span>Distance Covered (km):</span>
                    </label>
                    <input type="number" id="distanceInput" name="distanceInput" 
                           min="0.1" step="0.1" placeholder="0.0" required />
                </div>
            </fieldset>
            
            <fieldset id="driving-conditions">
                <legend>Driving Conditions</legend>
                <div class="label-input">
                    <label for="weatherInput">
                        <span>Weather Conditions:</span>
                    </label>
                    <select name="weatherInput" id="weatherInput" required>
                        <option value="">-- Select weather --</option>
                        <?php foreach ($weatherOptions as $option): ?>
                            <option value="<?= $option['id'] ?>">
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="label-input">
                    <label for="trafficInput">
                        <span>Traffic Conditions:</span>
                    </label>
                    <select name="trafficInput" id="trafficInput" required>
                        <option value="">-- Select traffic --</option>
                        <?php foreach ($trafficOptions as $option): ?>
                            <option value="<?= $option['id'] ?>">
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="label-input">
                    <label for="slipperinessInput">
                        <span>Road Slipperiness:</span>
                    </label>
                    <select name="slipperinessInput" id="slipperinessInput" required>
                        <option value="">-- Select condition --</option>
                        <?php foreach ($slipperinessOptions as $option): ?>
                            <option value="<?= $option['id'] ?>">
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="label-input">
                    <label for="lightInput">
                        <span>Light Conditions:</span>
                    </label>
                    <select name="lightInput" id="lightInput" required>
                        <option value="">-- Select light --</option>
                        <?php foreach ($lightOptions as $option): ?>
                            <option value="<?= $option['id'] ?>">
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Optional Maneuvers</legend>
                <div id="maneuver-wrapper">
                    <div class="maneuver-row">
                        <select name="maneuvers[]">
                            <option value="">-- Select maneuver (optional) --</option>
                            <option value="parking">Parking</option>
                            <option value="lane_change">Lane Change</option>
                            <option value="roundabout">Roundabout</option>
                            <option value="reverse">Reverse Driving</option>
                            <option value="hill_start">Hill Start</option>
                        </select>
                        <input type="number" name="quantities[]" min="1" placeholder="Qty (optional)" />
                    </div>
                </div>
                <button type="button" id="add-maneuver-btn">Add Another Maneuver</button>
            </fieldset>
            
            <div class="form-actions">
                <button type="button" id="save-btn" class="btn-primary">SAVE</button>
                <button type="reset" class="btn-secondary">RESET</button>
            </div>
        </form>
    </main>
    <footer>
        <p>Website by <b>Luis Markus Torres</b>. All rights reserved.</p>
    </footer>
</body>
</html>
