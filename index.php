<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

$db = new Database();

// Fetch options from database for select lists
$weatherOptions = [
    ['id' => 0, 'name' => 'Clear'],
    ['id' => 1, 'name' => 'Sunny'],
    ['id' => 2, 'name' => 'Cloudy'],
    ['id' => 3, 'name' => 'Foggy'],
    ['id' => 4, 'name' => 'Rainy'],
    ['id' => 5, 'name' => 'Snowy'],
    ['id' => 6, 'name' => 'Haily'],
    ['id' => 7, 'name' => 'Windy']
];

$trafficOptions = [
    ['id' => 0, 'name' => 'Sparse'],
    ['id' => 1, 'name' => 'Medium'],
    ['id' => 2, 'name' => 'Heavy']
];

$slipperinessOptions = [
    ['id' => 0, 'name' => 'Dry'],
    ['id' => 1, 'name' => 'Damp'],
    ['id' => 2, 'name' => 'Wet'],
    ['id' => 3, 'name' => 'Icy']
];

$lightOptions = [
    ['id' => 0, 'name' => 'Low'],
    ['id' => 1, 'name' => 'Medium'],
    ['id' => 2, 'name' => 'Bright']
];

// Calculate total distance
$totalKm = 0;
$result = $db->query("SELECT SUM(distance_km) as total FROM driving_experiences");
$row = $result->fetch();
if ($row) {
    $totalKm = $row['total'] ?? 0;
}

// Check for success message
$successMessage = '';
if (isset($_GET['success'])) {
    $successMessage = '✅ Driving experience saved successfully!';
} elseif (isset($_GET['updated'])) {
    $successMessage = '✅ Driving experience updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $successMessage = '✅ Driving experience deleted successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Driving Form</title>
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
      <?php if ($successMessage): ?>
        <p class="distance-info success"><?= htmlspecialchars($successMessage) ?></p>
      <?php else: ?>
        <p class="distance-info"></p>
      <?php endif; ?>
      <p class="detailed-info">
        Total distance: <strong><?= number_format($totalKm, 1) ?> km</strong> | 
        You can see more detailed information
        <b><a href="statistics.php" id="stats-link">here</a></b>
      </p>
    </header>
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
            <input type="number" id="distanceInput" name="distanceInput" min="0.1" step="0.1" placeholder="e.g., 15.5" required />
          </div>
        </fieldset>
        <fieldset id="driving-conditions">
          <legend>Driving Conditions</legend>
          <div class="label-input">
            <label for="weatherInput">
              <span>Weather Conditions:</span>
            </label>
            <select name="weatherInput" id="weatherInput" required>
              <option value="">--Select--</option>
              <?php foreach ($weatherOptions as $option): ?>
                <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="label-input">
            <label for="trafficInput">
              <span>Traffic Conditions:</span>
            </label>
            <select name="trafficInput" id="trafficInput" required>
              <option value="">--Select--</option>
              <?php foreach ($trafficOptions as $option): ?>
                <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="label-input">
            <label for="slipperinessInput">
              <span>Road Slipperiness:</span>
            </label>
            <select name="slipperinessInput" id="slipperinessInput" required>
              <option value="">--Select--</option>
              <?php foreach ($slipperinessOptions as $option): ?>
                <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="label-input">
            <label for="lightInput">
              <span>Light Conditions:</span>
            </label>
            <select name="lightInput" id="lightInput" required>
              <option value="">--Select--</option>
              <?php foreach ($lightOptions as $option): ?>
                <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></option>
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
              <input 
                type="number"
                name="quantities[]"
                min="1"
                placeholder="Qty (optional)"
              />
            </div>
          </div>
          <button type="button" id="add-maneuver-btn">Add Another Maneuver</button>
        </fieldset>
        <div class="form-actions">
          <button type="button" id="save-btn" class="btn-primary">SAVE!</button>
          <button type="reset" class="btn-secondary">Clear Form</button>
        </div>
      </form>
    </main>
    <footer>
      <p id="credits">
        Website by <b>Luis Markus Torres</b>. All rights reserved.
      </p>
      <p>
        You may see this project's repositoty on 
        <b><a href="https://github.com/LuisMarkusTorres/BackEndFinalProject" target="_blank">GitHub</a></b>
      </p>
    </footer>
    <div id="road">
      <div id="road-lines"></div>
    </div>
  </body>
</html>