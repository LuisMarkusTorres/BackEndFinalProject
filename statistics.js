const counts = countsJSON;
const entries = entriesJSON;

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
  const canvas = document.getElementById(id);
  if (!canvas) {
    console.warn(`Canvas not found: ${id}`);
    return;
  }

  new Chart(canvas, {
    type: "doughnut",
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: [
          "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0",
          "#9966FF", "#FF9F40", "#FF6384", "#C9CBCF"
        ],
        borderWidth: 2
      }]
    },
    options: {
      radius: '70%',
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 10,
            font: { size: 11 }
          }
        }
      }
    }
  });
}

function createBarChart(id, labels, data) {
  const canvas = document.getElementById(id);
  if (!canvas) {
    console.warn(`Canvas not found: ${id}`);
    return;
  }

  new Chart(canvas, {
    type: "bar",
    data: {
      labels,
      datasets: [{
        label: "Count",
        data,
        backgroundColor: "#36A2EB",
        borderColor: "#2E8BC0",
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });
}

function getLabel(group, id) {
  const maps = {
    weather: weatherLabels,
    traffic: trafficLabels,
    slipperiness: slipperinessLabels,
    light: lightLabels
  };
  return maps[group]?.[id] ?? "Unknown";
}

function formatManeuvers(row) {
  if (!row.maneuvers) return "—";

  const names = row.maneuvers.split(",");
  const quantities = row.quantities ? row.quantities.split(",") : [];

  return names.map((name, i) =>
    `${maneuverLabels[name] || name} (${quantities[i] || 1})`
  ).join(", ");
}

let dataTable;

$(document).ready(function () {
  // Initialize jQuery UI Tabs
  $("#stats-tabs").tabs({
    active: 0,
    collapsible: false,
    activate: function(event, ui) {
      // Add animation effect
      ui.newPanel.hide().fadeIn(400);
    }
  });

  // Initialize jQuery UI Accordion
  $("#conditions-accordion").accordion({
    collapsible: true,
    active: 0,
    heightStyle: "content",
    animate: 300
  });

  // Initialize jQuery UI Datepickers
  $("#date-from, #date-to").datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    maxDate: new Date(),
    onSelect: function() {
      if (dataTable) {
        dataTable.draw();
      }
    }
  });

  // Clear date filters
  $("#clear-dates").button().click(function() {
    $("#date-from, #date-to").val("");
    if (dataTable) {
      dataTable.draw();
    }
  });

  // Show KM details dialog
  $("#show-km-details").button({
    icon: "ui-icon-info"
  }).click(function() {
    const distances = entries.map(e => parseFloat(e.distance_km));
    const totalKm = distances.reduce((a, b) => a + b, 0);
    const avgKm = totalKm / entries.length;
    const maxKm = Math.max(...distances);
    const minKm = Math.min(...distances);

    $("#modal-total-km").text(totalKm.toFixed(1) + " km");
    $("#modal-total-entries").text(entries.length);
    $("#modal-avg-km").text(avgKm.toFixed(1) + " km");
    $("#modal-max-km").text(maxKm.toFixed(1) + " km");
    $("#modal-min-km").text(minKm.toFixed(1) + " km");

    $("#km-details-dialog").dialog({
      modal: true,
      width: 400,
      buttons: {
        "Close": function() {
          $(this).dialog("close");
        }
      }
    });
  });

  // Show notification dialog if message exists
  if ($("#message-dialog").length) {
    $("#message-dialog").dialog({
      modal: true,
      width: 350,
      buttons: {
        "OK": function() {
          $(this).dialog("close");
        }
      },
      open: function() {
        setTimeout(() => {
          $(this).dialog("close");
        }, 3000);
      }
    });
  }

  // Custom filtering function for date range
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    const dateFrom = $("#date-from").val();
    const dateTo = $("#date-to").val();
    const rowDate = data[0]; // Date column

    if (!dateFrom && !dateTo) {
      return true;
    }

    if (dateFrom && rowDate < dateFrom) {
      return false;
    }

    if (dateTo && rowDate > dateTo) {
      return false;
    }

    return true;
  });

  // Prepare table data
  const tableData = entries.map(row => {
    const code = experienceCodes[row.id] || "";

    return [
      row.entry_date,
      row.start_time,
      row.end_time,
      row.distance_km + " km",
      getLabel("weather", row.weather_id),
      getLabel("traffic", row.traffic_id),
      getLabel("slipperiness", row.slipperiness_id),
      getLabel("light", row.light_id),
      formatManeuvers(row),
      `
      <div class="action-buttons">
        <a href="edit.php?code=${code}" class="btn-edit" title="Edit">✏️</a>
        <a href="#" class="btn-delete" data-code="${code}" title="Delete">🗑️</a>
      </div>`
    ];
  });

  // Initialize DataTable
  dataTable = $("#experiences-table").DataTable({
    data: tableData,
    order: [[0, "desc"]],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    responsive: true,
    columnDefs: [
      { orderable: false, targets: 9 }
    ],
    language: {
      search: "Search experiences:",
      lengthMenu: "Show _MENU_ experiences per page",
      info: "Showing _START_ to _END_ of _TOTAL_ experiences",
      infoEmpty: "No experiences found",
      zeroRecords: "No matching experiences found",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous"
      }
    }
  });

  // Delete confirmation with jQuery UI Dialog
  $(document).on("click", ".btn-delete", function(e) {
    e.preventDefault();
    const code = $(this).data("code");
    const deleteUrl = `delete.php?code=${code}`;

    $("#delete-dialog").dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Delete": function() {
          window.location.href = deleteUrl;
        },
        "Cancel": function() {
          $(this).dialog("close");
        }
      }
    });
  });

  // Create charts after a small delay to ensure tabs/accordion are rendered
  setTimeout(() => {
    createPieChart("weather-doughnut-chart", weatherLabels, Object.values(counts.weather));
    createPieChart("traffic-doughnut-chart", trafficLabels, Object.values(counts.traffic));
    createPieChart("slipperiness-doughnut-chart", slipperinessLabels, Object.values(counts.slipperiness));
    createPieChart("light-doughnut-chart", lightLabels, Object.values(counts.light));

    const maneuverKeys = Object.keys(maneuverCounts);
    const maneuverValues = Object.values(maneuverCounts);
    const maneuverReadable = maneuverKeys.map(k => maneuverLabels[k] || k);

    if (maneuverKeys.length > 0) {
      createPieChart("maneuver-doughnut-chart", maneuverReadable, maneuverValues);
      createBarChart("maneuver-bar-chart", maneuverReadable, maneuverValues);
    }
  }, 100);
});
