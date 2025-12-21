<?php
require_once 'config.inc.php';
require_once 'Database.php';
require_once 'DrivingExperience.php';

$db = new Database();

/**
 * Fetch all driving experiences
 * MUST query: driving_experiences (plural)
 */
$entries = DrivingExperience::getAll($db);

/**
 * Init session storage for anonymous codes
 */
if (!isset($_SESSION['experience_codes'])) {
    $_SESSION['experience_codes'] = [];
}

/**
 * Generate anonymous codes (stable per session)
 */
foreach ($entries as $entry) {
    if (!in_array($entry['id'], $_SESSION['experience_codes'], true)) {
        $code = bin2hex(random_bytes(8));
        $_SESSION['experience_codes'][$code] = $entry['id'];
    }
}

/**
 * Main category counts
 */
$counts = [
    'weather'       => array_fill(0, 8, 0),
    'traffic'       => array_fill(0, 3, 0),
    'slipperiness'  => array_fill(0, 4, 0),
    'light'         => array_fill(0, 3, 0),
];

/**
 * Maneuver counts (dynamic keys)
 */
$maneuverCounts = [];

$totalKm = 0.0;

/**
 * Aggregate statistics
 */
foreach ($entries as $exp) {
    // Defensive checks (avoid notices)
    if (isset($exp['weather_id'])) {
        $counts['weather'][(int)$exp['weather_id']]++;
    }
    if (isset($exp['traffic_id'])) {
        $counts['traffic'][(int)$exp['traffic_id']]++;
    }
    if (isset($exp['slipperiness_id'])) {
        $counts['slipperiness'][(int)$exp['slipperiness_id']]++;
    }
    if (isset($exp['light_id'])) {
        $counts['light'][(int)$exp['light_id']]++;
    }

    $totalKm += (float)$exp['distance_km'];

    /**
     * Maneuvers parsing
     * maneuvers   = "parking,lane_change"
     * quantities  = "3,5"
     */
    if (!empty($exp['maneuvers']) && !empty($exp['quantities'])) {
        $maneuvers  = array_map('trim', explode(',', $exp['maneuvers']));
        $quantities = array_map('trim', explode(',', $exp['quantities']));

        foreach ($maneuvers as $i => $maneuver) {
            $qty = isset($quantities[$i]) ? (int)$quantities[$i] : 0;

            if (!isset($maneuverCounts[$maneuver])) {
                $maneuverCounts[$maneuver] = 0;
            }
            $maneuverCounts[$maneuver] += $qty;
        }
    }
}

/**
 * JSON for JS
 */
$countsJSON     = json_encode($counts, JSON_THROW_ON_ERROR);
$entriesJSON    = json_encode($entries, JSON_THROW_ON_ERROR);
$maneuverJSON   = json_encode($maneuverCounts, JSON_THROW_ON_ERROR);

/**
 * Flash messages
 */
$successMessage = '';
if (isset($_GET['updated'])) {
    $successMessage = '✅ Driving experience updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $successMessage = '✅ Driving experience deleted successfully!';
} elseif (isset($_GET['error'])) {
    $successMessage = '⛔ Error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving Experiences Summary</title>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="statistics.css">

    <script>
        const countsJSON      = <?= $countsJSON ?>;
        const entriesJSON     = <?= $entriesJSON ?>;
        const maneuverCounts  = <?= $maneuverJSON ?>;
        const experienceCodes = <?= json_encode(array_flip($_SESSION['experience_codes'])) ?>;
    </script>

    <script src="statistics.js" defer></script>
</head>
<body>

<header>
    <h1>Driving Statistics</h1>
    <hr>

    <?php if ($successMessage): ?>
        <div id="message-dialog" title="Notification" style="display:none;">
            <p><?= strip_tags($successMessage) ?></p>
        </div>
    <?php endif; ?>

    <p>
        Total km driven:
        <strong id="total-km-display"><?= number_format($totalKm, 1) ?> km</strong>
        <button id="show-km-details" class="info-btn" type="button">ℹ️ Details</button>
    </p>

    <p>
        <a href="index.php">← Back to main page</a>
    </p>
</header>

<main>
    <section>
        <h2>All Driving Experiences</h2>
        
        <div class="filter-controls">
            <label>Quick Filter by Date Range:</label>
            <input type="text" id="date-from" placeholder="From Date" readonly>
            <input type="text" id="date-to" placeholder="To Date" readonly>
            <button id="clear-dates">Clear</button>
        </div>

        <div class="table-container">
            <table id="experiences-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Distance</th>
                        <th>Weather</th>
                        <th>Traffic</th>
                        <th>Slipperiness</th>
                        <th>Light</th>
                        <th>Maneuvers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Visual Statistics</h2>

        <div id="stats-tabs">
            <ul>
                <li><a href="#tab-conditions">Driving Conditions</a></li>
                <li><a href="#tab-maneuvers">Maneuvers</a></li>
            </ul>

            <div id="tab-conditions">
                <div id="conditions-accordion">
                    <h3>Weather Conditions</h3>
                    <div>
                        <div class="chart-wrapper">
                            <canvas id="weather-doughnut-chart"></canvas>
                        </div>
                    </div>

                    <h3>Traffic Conditions</h3>
                    <div>
                        <div class="chart-wrapper">
                            <canvas id="traffic-doughnut-chart"></canvas>
                        </div>
                    </div>

                    <h3>Road Slipperiness</h3>
                    <div>
                        <div class="chart-wrapper">
                            <canvas id="slipperiness-doughnut-chart"></canvas>
                        </div>
                    </div>

                    <h3>Light Conditions</h3>
                    <div>
                        <div class="chart-wrapper">
                            <canvas id="light-doughnut-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-maneuvers">
                <h3>Maneuvers Performed</h3>
                <div class="chart-wrapper">
                    <canvas id="maneuver-doughnut-chart"></canvas>
                </div>
                <div class="chart-wrapper" style="margin-top: 2rem;">
                    <canvas id="maneuver-bar-chart"></canvas>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Hidden dialog for KM details -->
<div id="km-details-dialog" title="Distance Statistics" style="display:none;">
    <p><strong>Total Distance:</strong> <span id="modal-total-km"></span></p>
    <p><strong>Total Entries:</strong> <span id="modal-total-entries"></span></p>
    <p><strong>Average per Entry:</strong> <span id="modal-avg-km"></span></p>
    <p><strong>Longest Drive:</strong> <span id="modal-max-km"></span></p>
    <p><strong>Shortest Drive:</strong> <span id="modal-min-km"></span></p>
</div>

<!-- Delete confirmation dialog -->
<div id="delete-dialog" title="Confirm Deletion" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
    Are you sure you want to delete this driving experience? This action cannot be undone.</p>
</div>

<footer>
    <p>Website by <strong>Luis Markus Torres</strong></p>
</footer>

</body>
</html>
