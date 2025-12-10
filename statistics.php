<?php
require "db.php";

// Fetch all driving entries
$query = $pdo->query("SELECT * FROM driving_experiences");
$entries = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch ALL maneuver rows
$manQ = $pdo->query("SELECT maneuver, quantity FROM driving_maneuvers");
$maneuverRows = $manQ->fetchAll(PDO::FETCH_ASSOC);

// Compute counts for main categories
$counts = [
    "weather" => [],
    "traffic" => [],
    "slipperiness" => [],
    "light" => []
];

$totalKm = 0;

foreach ($entries as $exp) {
    $counts["weather"][$exp["weather_id"]] =
        ($counts["weather"][$exp["weather_id"]] ?? 0) + 1;

    $counts["traffic"][$exp["traffic_id"]] =
        ($counts["traffic"][$exp["traffic_id"]] ?? 0) + 1;

    $counts["slipperiness"][$exp["slipperiness_id"]] =
        ($counts["slipperiness"][$exp["slipperiness_id"]] ?? 0) + 1;

    $counts["light"][$exp["light_id"]] =
        ($counts["light"][$exp["light_id"]] ?? 0) + 1;

    $totalKm += $exp["distance_km"];
}

// Count maneuvers
$maneuverCounts = [];

foreach ($maneuverRows as $m) {
    if ($m['maneuver'] !== "") {
        $maneuverCounts[$m['maneuver']] =
            ($maneuverCounts[$m['maneuver']] ?? 0) + $m['quantity'];
    }
}

// Send data to JS
$countsJSON = json_encode($counts);
$entriesJSON = json_encode($entries);
$maneuverCountsJSON = json_encode($maneuverCounts);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Driving Experiences Summary</title>
    <script>
        const counts = <?= $countsJSON ?>;
        const entries = <?= $entriesJSON ?>;
        const maneuverCounts = <?= $maneuverCountsJSON ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="statistics.js" defer></script>
    <link rel="stylesheet" href="statistics.css" />
  </head>

  <body>
    <header>
      <h1>Driving Statistics</h1>
      <hr />
      <p id="total-km">Total km driven: <?= $totalKm ?> 🚦</p>
      <p>
        Click <b><a href="final_project.html">here</a></b> to return to the main page.
      </p>
    </header>

    <main>
      <div id="charts-grid">
        <div class="chart-container">
          <h3>Weather Conditions</h3>
          <canvas id="weather-doughnut-chart"></canvas>
          <canvas id="weather-bar-chart"></canvas>
        </div>

        <div class="chart-container">
          <h3>Traffic Conditions</h3>
          <canvas id="traffic-doughnut-chart"></canvas>
          <canvas id="traffic-bar-chart"></canvas>
        </div>

        <div class="chart-container">
          <h3>Road Slipperiness</h3>
          <canvas id="slipperiness-doughnut-chart"></canvas>
          <canvas id="slipperiness-bar-chart"></canvas>
        </div>

        <div class="chart-container">
          <h3>Light Conditions</h3>
          <canvas id="light-doughnut-chart"></canvas>
          <canvas id="light-bar-chart"></canvas>
        </div>

        <!-- NEW MANEUVER CHART -->
        <div class="chart-container">
          <h3>Maneuvers Performed</h3>
          <canvas id="maneuver-bar-chart"></canvas>
        </div>
      </div>
    </main>

    <footer>
      <p>Website by <b>Luis Markus Torres</b>. All rights reserved.</p>
      <p>
        You may contact me via
        <b><a href="https://www.linkedin.com/in/luismarkustorres/" target="_blank">LinkedIn</a></b>
      </p>
    </footer>

  </body>
</html>
