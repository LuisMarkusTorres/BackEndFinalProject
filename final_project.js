// jQuery UI initialization
$(document).ready(function() {
  // Enable jQuery UI Datepicker on date input
  $("#drivingDateInput").datepicker({
    dateFormat: 'yy-mm-dd',
    maxDate: 0, // Can't select future dates
    changeMonth: true,
    changeYear: true,
    yearRange: "-5:+0",
    showButtonPanel: true,
    beforeShow: function(input, inst) {
      // Add custom styling class
      setTimeout(function() {
        inst.dpDiv.addClass('custom-datepicker');
      }, 0);
    }
  });
  
  // Also enable on edit page if exists
  if ($("#drivingDateInput").length) {
    // Set the datepicker to use the input's type=date format
    const dateInput = document.getElementById("drivingDateInput");
    if (dateInput && dateInput.value) {
      $("#drivingDateInput").datepicker("setDate", dateInput.value);
    }
  }
});

// Vanilla JavaScript for form validation and interactions
const form = document.querySelector("form");
const saveBtn = document.getElementById("save-btn");
const distanceInfoPara = document.querySelector(".distance-info");

function showMessage(text) {
  distanceInfoPara.textContent = text;
  
  // Auto-hide success messages after 5 seconds
  if (text.includes("✅") || text.includes("Saving")) {
    setTimeout(() => {
      distanceInfoPara.textContent = "";
    }, 5000);
  }
}

saveBtn.addEventListener("click", (event) => {
  // Prevent submit until validation passes
  if (!form.checkValidity()) {
    event.preventDefault();
    form.reportValidity();
    return;
  }

  // Custom rule: end time must be later than start time
  const start = document.getElementById("startTimeInput").value;
  const end = document.getElementById("endTimeInput").value;
  if (end <= start) {
    event.preventDefault();
    showMessage("⛔ End time must be later than start time.");
    return;
  }

  // Custom rule: distance must be positive
  const dist = Number(document.getElementById("distanceInput").value);
  if (dist <= 0) {
    event.preventDefault();
    showMessage("⛔ Distance must be greater than 0.");
    return;
  }

  // If everything is correct, submit the form
  showMessage("Saving your driving experience... 🚗");
  form.submit();
});

// Add maneuver functionality
document.getElementById("add-maneuver-btn").addEventListener("click", () => {
  const wrapper = document.getElementById("maneuver-wrapper");
  const newRow = document.createElement("div");
  newRow.classList.add("maneuver-row");
  newRow.innerHTML = `
    <select name="maneuvers[]">
      <option value="">-- Select maneuver (optional) --</option>
      <option value="parking">Parking</option>
      <option value="lane_change">Lane Change</option>
      <option value="roundabout">Roundabout</option>
      <option value="reverse">Reverse Driving</option>
      <option value="hill_start">Hill Start</option>
    </select>
    <input type="number" name="quantities[]" min="1" placeholder="Qty (optional)" />
    <button type="button" class="remove-maneuver">X</button>
  `;
  wrapper.appendChild(newRow);

  // Add remove functionality to the new row
  newRow.querySelector(".remove-maneuver").addEventListener("click", () => {
    newRow.remove();
  });
});

// Add remove functionality to existing maneuver rows (for edit page)
document.addEventListener("DOMContentLoaded", () => {
  const existingRemoveButtons = document.querySelectorAll(".remove-maneuver");
  existingRemoveButtons.forEach(button => {
    button.addEventListener("click", (e) => {
      e.target.closest(".maneuver-row").remove();
    });
  });
});