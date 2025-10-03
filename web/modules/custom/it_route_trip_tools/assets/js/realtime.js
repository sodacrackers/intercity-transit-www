import _ from "https://cdn.jsdelivr.net/npm/lodash-es@4.17.21/lodash.min.js";
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
  fetch(
    "https://its.rideralerts.com/InfoPoint/GTFS-Realtime.ashx?Type=TripUpdate&debug=true"
  )
    .then((res) => res.json())
    .then((feed) => {
      currentFeedData = feed; // Store the feed data for helper functions
      updateRealtimeData();
    })
    .catch((err) => {
      clearRealtimeData();
      console.error("Realtime fetch failed", err);
    });
}

/**
 * Updates elements with realtime delay information
 */
function updateRealtimeData() {
  // Find all realtime data divs for this route
  const realtimeElements = container.querySelectorAll(
    `.realtime[data-route-id][data-stop-id]`
  );

  for (const element of realtimeElements) {
    const routeId = element.getAttribute("data-route-id");
    if (!routeId) continue;
    const stopId = element.getAttribute("data-stop-id");
    if (!stopId) continue;

    // Get the delay for this stop using the helper function
    const upcoming = getStopTimes(routeId, stopId);

    upcoming.forEach((r) => {
      let delay = formatDelayTime(r.delayMinutes);
      let isLate = r.delayMinutes < 0;
      if (isLate) {
        delay = `<span class="text-danger">${delay}</span>`;
      }
      element.innerHTML = `Bus ${r.bus} at ${r.arrivalTime} (${delay})<br>`;
    });
  }
}

/**
 * Gets upcoming stop times for a specific route and stop from the feed data
 * @param {string} routeId - The route ID
 * @param {string} stopId - The stop ID
 * @returns {Array|null} Array of upcoming stop times or null if no data
 */
function getStopTimes(routeId, stopId) {
  console.log(_.VERSION); // "4.17.21"
  console.log(_.get({}, "x")); // u
  if (!currentFeedData) return null;

  // Get entities for this route
  const routeEntities = _.filter(
    currentFeedData.Entities,
    (e) => String(e.TripUpdate?.Trip?.RouteId) === String(routeId)
  );

  // Flatten stops for this stopId
  const stopMatches = _.chain(routeEntities)
    .map((e) => {
      const vehicle = _.get(e, "TripUpdate.Vehicle.Label", "Unknown");

      const stopUpdates = _.filter(
        e.TripUpdate?.StopTimeUpdates || [],
        (s) => String(s.StopId) === String(stopId)
      );

      return _.map(stopUpdates, (s) => ({
        bus: vehicle,
        arrivalTime: s.Arrival?.Time
          ? new Date(s.Arrival.Time * 1000).toLocaleTimeString()
          : "N/A",
        delayMinutes: s.Arrival?.Delay || 0,
      }));
    })
    .flatten()
    .value();

  return stopMatches;
}

/**
 * Formats delay time in seconds to human-readable format
 * @param {number|null} seconds - Delay in seconds
 * @returns {string} Formatted delay string
 */
function formatDelayTime(seconds) {
  if (seconds === null) {
    return "—";
  }

  const delayMinutes = Math.round(seconds / 60);

  if (delayMinutes === 0) {
    return "On time";
  } else if (delayMinutes > 0) {
    return `+${delayMinutes} min early`;
  } else {
    return `${delayMinutes} min late`;
  }
}
