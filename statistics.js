// =========================
//  READ PHP-INJECTED DATA
// =========================
const counts = JSON.parse(countsJSON);
const entries = JSON.parse(entriesJSON);

// =========================
//  LABELS
// =========================
const weatherLabels = ["Clear", "Sunny", "Cloudy", "Foggy", "Rainy", "Snowy", "Haily", "Windy"];
const trafficLabels = ["Sparse", "Medium", "Heavy"];
const slipperinessLabels = ["Dry", "Damp", "Wet", "Icy"];
const lightLabels = ["Low", "Medium", "Bright"];

const maneuverLabels = {
    parking: "Parking",
    lane_change: "Lane Change",
    roundabout: "Roundabout",
    reverse: "Reverse Driving",
    hill_start: "Hill Start"
};

// =========================
//  COLLECT MANEUVER COUNTS
// =========================
const maneuverCounts = {
    parking: 0,
    lane_change: 0,
    roundabout: 0,
    reverse: 0,
    hill_start: 0
};

entries.forEach(entry => {
    if (!entry.maneuvers || !entry.quantities) return;

    const m = JSON.parse(entry.maneuvers);
    const q = JSON.parse(entry.quantities);

    m.forEach((maneuver, index) => {
        const qty = parseInt(q[index] || 1);
        if (maneuverCounts[maneuver] !== undefined) {
            maneuverCounts[maneuver] += qty;
        }
    });
});

// =========================
//  CHARTING HELPERS
// =========================

function createPieChart(id, labels, data) {
    new Chart(document.getElementById(id), {
        type: "pie",
        data: {
            labels,
            datasets: [{
                data,
                borderWidth: 1
            }]
        },
        options: { responsive: true }
    });
}

function createBarChart(id, labels, data) {
    new Chart(document.getElementById(id), {
        type: "bar",
        data: {
            labels,
            datasets: [{
                label: "Count",
                data,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
}

// =========================
//  CREATE ALL MAIN CHARTS
// =========================
createPieChart("weather-doughnut-chart", weatherLabels, Object.values(counts.weather));
createBarChart("weather-bar-chart", weatherLabels, Object.values(counts.weather));

createPieChart("traffic-doughnut-chart", trafficLabels, Object.values(counts.traffic));
createBarChart("traffic-bar-chart", trafficLabels, Object.values(counts.traffic));

createPieChart("slipperiness-doughnut-chart", slipperinessLabels, Object.values(counts.slipperiness));
createBarChart("slipperiness-bar-chart", slipperinessLabels, Object.values(counts.slipperiness));

createPieChart("light-doughnut-chart", lightLabels, Object.values(counts.light));
createBarChart("light-bar-chart", lightLabels, Object.values(counts.light));

// =========================
//  MANEUVER CHARTS
// =========================
const maneuverKeys = Object.keys(maneuverCounts);
const maneuverValues = Object.values(maneuverCounts);
const maneuverReadable = maneuverKeys.map(k => maneuverLabels[k]);

createPieChart("maneuver-doughnut-chart", maneuverReadable, maneuverValues);
createBarChart("maneuver-bar-chart", maneuverReadable, maneuverValues);

// =========================
//  BUILD DATA TABLE
// =========================

function getLabel(group, id) {
    const maps = {
        weather: weatherLabels,
        traffic: trafficLabels,
        slipperiness: slipperinessLabels,
        light: lightLabels
    };
    return maps[group][id] || "Unknown";
}

function formatManeuvers(row) {
    if (!row.maneuvers) return "—";
    const m = JSON.parse(row.maneuvers);
    const q = JSON.parse(row.quantities);
    return m.map((name, i) => `${maneuverLabels[name]} (${q[i]})`).join(", ");
}

function buildTable() {
    const table = document.querySelector("table");
    const tbody = document.createElement("tbody");

    entries.forEach(row => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${row.entry_date}</td>
            <td>${row.start_time}</td>
            <td>${row.end_time}</td>
            <td>${row.distance_km} km</td>
            <td>${getLabel("weather", row.weather_id)}</td>
            <td>${getLabel("traffic", row.traffic_id)}</td>
            <td>${getLabel("slipperiness", row.slipperiness_id)}</td>
            <td>${getLabel("light", row.light_id)}</td>
            <td>${formatManeuvers(row)}</td>
        `;

        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
}

buildTable();
