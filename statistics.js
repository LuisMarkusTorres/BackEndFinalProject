console.log("Counts:", counts);
console.log("Entries:", entries);
console.log("Maneuver Totals:", maneuverCounts);

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

createPieChart("weather-doughnut-chart", weatherLabels, Object.values(counts.weather));
createBarChart("weather-bar-chart", weatherLabels, Object.values(counts.weather));

createPieChart("traffic-doughnut-chart", trafficLabels, Object.values(counts.traffic));
createBarChart("traffic-bar-chart", trafficLabels, Object.values(counts.traffic));

createPieChart("slipperiness-doughnut-chart", slipperinessLabels, Object.values(counts.slipperiness));
createBarChart("slipperiness-bar-chart", slipperinessLabels, Object.values(counts.slipperiness));

createPieChart("light-doughnut-chart", lightLabels, Object.values(counts.light));
createBarChart("light-bar-chart", lightLabels, Object.values(counts.light));

const maneuverKeys = Object.keys(maneuverCounts);
const maneuverValues = Object.values(maneuverCounts);
const maneuverReadable = maneuverKeys.map(k => maneuverLabels[k]);


createBarChart("maneuver-bar-chart", maneuverReadable, maneuverValues);

function getLabel(group, id) {
    const maps = {
        weather: weatherLabels,
        traffic: trafficLabels,
        slipperiness: slipperinessLabels,
        light: lightLabels
    };
    return maps[group][id] || "Unknown";
}

function buildTable() {
    const table = document.querySelector("table");
    if (!table) return;

    const tbody = document.createElement("tbody");

    entries.forEach(row => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${row.driving_date}</td>
            <td>${row.start_time}</td>
            <td>${row.end_time}</td>
            <td>${row.distance_km} km</td>
            <td>${getLabel("weather", row.weather_id)}</td>
            <td>${getLabel("traffic", row.traffic_id)}</td>
            <td>${getLabel("slipperiness", row.slipperiness_id)}</td>
            <td>${getLabel("light", row.light_id)}</td>
            <td>—</td> <!-- Maneuvers per-entry not stored -->
        `;

        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
}

buildTable();
