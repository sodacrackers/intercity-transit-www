import _ from "https://cdn.jsdelivr.net/npm/lodash-es@4.17.21/lodash.min.js";

console.log("Lodash ES version:", _.VERSION);

/**
 * Realtime Transit Data Display.
 *
 * This script listens for a click to "Realtime" button on a route page. When clicked,
 * we fetch realtime data from the API, then update each .realtime div with
 * the latest arrival/ departure time, delay, and vehicle info.
 *
 * Basic flow:
 * 1. Click "Realtime" button
 * 2. followRealtime() to auto-refresh every 30 seconds
 * 3. updateRealtimeData() to update each .realtime div
 * 4. clearRealtimeData() to stop refrteshing and clear data
 *
 * @author Intercity Transit
 * @version 1.0
 */

// Store the feed data for helper functions to access
let currentFeedData = null;
let refreshInterval = null;
let container = null;
let routeId = null;

Drupal.behaviors.route_realtime = {
  attach(context, settings) {
    // Find the container with timetables
    container = context.querySelector("#schedule");
    if (!container) return;

    // Attach button handler to labels, not inputs
    const labels = once(
      "display-stops-options-labels",
      ".btn-group.btn-stops-toggle label",
      context
    );

    // Show realtime if selected
    for (const label of labels) {
      label.addEventListener("click", () => {
        const input = label.querySelector(
          'input[name="display_stops_options"]'
        );
        if (!input) return;

        if (input.value === "realtime") {
          followRealtime();
        } else {
          clearRealtimeData();
        }
      });
    }
  },
};

/**
 * Clears all realtime data from elements and stops the refresh timer
 */
function clearRealtimeData() {
  currentFeedData = null;

  // Stop the refresh timer
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }

  // Clear all realtime elements
  if (!container) return;
  const elements = container.querySelectorAll(
    ".realtime[data-route-id][data-stop-id]"
  );
  elements.forEach((element) => {
    element.textContent = "";
  });
}

/**
 * Sets up automatic realtime data refresh every 30 seconds
 */
function followRealtime() {
  // Clear any existing interval
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }

  // Run once right away
  fetchRealtime();

  // Then auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    fetchRealtime();
  }, 30000);
}

/**
 * Fetches and refreshes realtime data for the route
 */
function fetchRealtime() {
  showSpinner();
  fetch(
    "https://its.rideralerts.com/InfoPoint/GTFS-Realtime.ashx?Type=TripUpdate&debug=true"
  )
    .then((res) => res.json())
    .then((feed) => {
      currentFeedData = feed; // Store the feed data for helper functions
      updateRealtimeData();
      hideSpinner();
    })
    .catch((err) => {
      clearRealtimeData();
      hideSpinner();
      console.error("Realtime fetch failed", err);
    });
}

/**
 * Updates elements with realtime delay information
 */
function updateRealtimeData() {
  const realtimeElements = container.querySelectorAll(
    `.realtime[data-stop-id]`
  );

  for (const element of realtimeElements) {
    const stopId = element.getAttribute("data-stop-id");
    if (!stopId) continue;

    const upcoming = getStopTimes(stopId);
    if (!upcoming || upcoming.length === 0) {
      element.innerHTML = "No arrivals available";
      continue;
    }

    // Group by route so we can show "Route X:" header
    const grouped = _.groupBy(upcoming, "routeId");

    let html = "";
    _.forEach(grouped, (arrivals, route) => {
      html += `<div class="route" data-route-id="${route}">
      <strong>Route ${route}:</strong>`;
      arrivals.forEach((r) => {
        const delay = () => {
          if (r.delay < 0) return { text: `early`, class: `label-success` };
          if (r.delay > 0) return { text: `running late`, class: `label-danger` };
          return { text: `on time`, class: `label-default` };
        };
        html += `<div>Bus ${r.bus}
        <span class="glyphicon glyphicon-time" aria-hidden="true"></span>${r.departureTime}
        <span class="small label ${delay().class}">${delay().text}</span></div>`;
      });
      html += `</div>`;
    });

    element.innerHTML = html;
  }
}

/**
 * Gets upcoming stop times for a specific route and stop from the feed data
 * @param {string} stopId - The stop ID
 * @returns {Array|null} Array of upcoming stop times or null if no data
 */
function getStopTimes(stopId) {
  if (!currentFeedData) return null;

  return _.chain(currentFeedData.Entities)
    .map((e) => {
      const vehicle = _.get(e, "TripUpdate.Vehicle.Label", "Unknown");
      const tripRouteId = _.get(e, "TripUpdate.Trip.RouteId", "??");

      return _.map(e.TripUpdate?.StopTimeUpdates || [], (s) => {
        if (String(s.StopId) !== String(stopId)) return null;

        return {
          bus: vehicle,
          routeId: tripRouteId,
          departureTime: s.Departure?.Time
            ? new Date(s.Departure.Time * 1000).toLocaleTimeString()
            : "N/A",
          delay: s.Departure?.Delay ?? null,
        };
      });
    })
    .flatten()
    .compact()
    .orderBy("departureTime")
    .value();
}

/**
 * Shows a loading spinner on the page
 */
function showSpinner() {
  const spinners = container.querySelectorAll(".realtime-loading-spinner");
  if (!spinners.length) return;
  spinners.forEach((spinner) => spinner.classList.remove("hidden"));
}

/**
 * Hides the loading spinner
 */
function hideSpinner() {
  const spinners = container.querySelectorAll(".realtime-loading-spinner");
  if (!spinners.length) return;
  spinners.forEach((spinner) => spinner.classList.add("hidden"));
}
