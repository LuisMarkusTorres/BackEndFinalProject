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
if (!$experience->loadById($id)) {
    header("Location: statistics.php?error=Experience+not+found");
    exit();
}

// Fetch dropdown options from lookup tables using JOIN-based methods
$weatherOptions = DrivingExperience::getWeatherConditions($db);
$trafficOptions = DrivingExperience::getTrafficLevels($db);
$slipperinessOptions = DrivingExperience::getRoadConditions($db);
$lightOptions = DrivingExperience::getLightConditions($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Driving Experience</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="main_page.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script type="module" src="final_project.js" defer></script>
</head>
<body>
    <header>
        <h1>Edit Driving Experience</h1>
        <hr />
        <p class="distance-info"></p>
        <p class="detailed-info">
            <b><a href="statistics.php">← Back to Statistics</a></b>
        </p>
    </header>
    <main>
        <form action="update.php" method="post">
            <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>" />
            
            <fieldset id="driving-info">
                <legend>Driving experience information</legend>
                <div class="label-input">
                    <label for="drivingDateInput">
                        <span>Driving Experience's Date:</span>
                    </label>
                    <input type="date" id="drivingDateInput" name="drivingDateInput" 
                           value="<?= htmlspecialchars($experience->entryDate) ?>" required />
                </div>
                <div class="label-input">
                    <label for="startTimeInput">
                        <span>Start Time:</span>
                    </label>
                    <input type="time" id="startTimeInput" name="startTimeInput" 
                           value="<?= htmlspecialchars($experience->startTime) ?>" required />
                </div>
                <div class="label-input">
                    <label for="endTimeInput">
                        <span>End Time:</span>
                    </label>
                    <input type="time" id="endTimeInput" name="endTimeInput" 
                           value="<?= htmlspecialchars($experience->endTime) ?>" required />
                </div>
                <div class="label-input">
                    <label for="distanceInput">
                        <span>Distance Covered (km):</span>
                    </label>
                    <input type="number" id="distanceInput" name="distanceInput" 
                           value="<?= htmlspecialchars($experience->distanceKm) ?>" 
                           min="0.1" step="0.1" required />
                </div>
            </fieldset>
            
            <fieldset id="driving-conditions">
                <legend>Driving Conditions</legend>
                <div class="label-input">
                    <label for="weatherInput">
                        <span>Weather Conditions:</span>
                    </label>
                    <select name="weatherInput" id="weatherInput" required>
                        <?php foreach ($weatherOptions as $option): ?>
                            <option value="<?= $option['id'] ?>" 
                                    <?= $experience->weatherId == $option['id'] ? 'selected' : '' ?>>
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
                        <?php foreach ($trafficOptions as $option): ?>
                            <option value="<?= $option['id'] ?>" 
                                    <?= $experience->trafficId == $option['id'] ? 'selected' : '' ?>>
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
                        <?php foreach ($slipperinessOptions as $option): ?>
                            <option value="<?= $option['id'] ?>" 
                                    <?= $experience->slipperinessId == $option['id'] ? 'selected' : '' ?>>
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
                        <?php foreach ($lightOptions as $option): ?>
                            <option value="<?= $option['id'] ?>" 
                                    <?= $experience->lightId == $option['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Optional Maneuvers</legend>
                <div id="maneuver-wrapper">
                    <?php if (empty($experience->maneuvers)): ?>
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
                    <?php else: ?>
                        <?php 
                        for ($i = 0; $i < count($experience->maneuvers); $i++):
                            $maneuverName = $experience->maneuvers[$i];
                            $quantity = $experience->quantities[$i] ?? 1;
                        ?>
                            <div class="maneuver-row">
                                <select name="maneuvers[]">
                                    <option value="">-- Select maneuver (optional) --</option>
                                    <option value="parking" <?= $maneuverName == 'parking' ? 'selected' : '' ?>>Parking</option>
                                    <option value="lane_change" <?= $maneuverName == 'lane_change' ? 'selected' : '' ?>>Lane Change</option>
                                    <option value="roundabout" <?= $maneuverName == 'roundabout' ? 'selected' : '' ?>>Roundabout</option>
                                    <option value="reverse" <?= $maneuverName == 'reverse' ? 'selected' : '' ?>>Reverse Driving</option>
                                    <option value="hill_start" <?= $maneuverName == 'hill_start' ? 'selected' : '' ?>>Hill Start</option>
                                </select>
                                <input type="number" name="quantities[]" value="<?= $quantity ?>" min="1" placeholder="Qty (optional)" />
                                <button type="button" class="remove-maneuver">X</button>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-maneuver-btn">Add Another Maneuver</button>
            </fieldset>
            
            <div class="form-actions">
                <button type="button" id="save-btn" class="btn-primary">UPDATE</button>
                <a href="statistics.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
    <footer>
        <p>Website by <b>Luis Markus Torres</b>. All rights reserved.</p>
    </footer>
</body>
</html>
