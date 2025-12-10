const form = document.querySelector("form");
const saveBtn = document.getElementById("save-btn");

// Display message at the top
const distanceInfoPara = document.querySelector(".distance-info");

function showMessage(text) {
    distanceInfoPara.textContent = text;
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
    const end   = document.getElementById("endTimeInput").value;

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

    // If everything is correct:
    showMessage("Saving your driving experience... 🚗");
});

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

  newRow.querySelector(".remove-maneuver").addEventListener("click", () => {
    newRow.remove();
  });
});
