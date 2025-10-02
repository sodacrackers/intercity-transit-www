/**
 * @file
 * Realtime route data integration for Intercity Transit.
 *
 * Fetches realtime JSON data from the GTFS-Realtime feed and appends
 * it to the route's timetable to show live trip updates and delays.
 */

(function () {
  "use strict";

  // Store the feed data for helper functions to access
  let cachedFeedData = null;
  let refreshInterval = null;

  Drupal.behaviors.route_realtime = {
    attach(context, settings) {
      const table = context.querySelector("#route-table table.timetable");
      const routeId = table?.getAttribute("data-route-id");
      if (!routeId || !table) return;

      // Attach to labels, not inputs
      const labels = once(
        "display-stops-options-labels",
        ".btn-group.btn-stops-toggle label",
        context
      );

      for (const label of labels) {
        label.addEventListener("click", () => {
          const input = label.querySelector(
            'input[name="display_stops_options"]'
          );
          if (!input) return;

          if (input.value === "realtime") {
            followRealtime(routeId, table);
          } else {
            clearRealtimeData(table);
          }
        });
      }
    },
  };

  /**
   * Fetches and refreshes realtime data for the route
   * @param {string} routeId - The route ID
   * @param {HTMLElement} table - The timetable element
   */
  function fetchRealtime(routeId, table) {
    fetch(
      "https://its.rideralerts.com/InfoPoint/GTFS-Realtime.ashx?Type=TripUpdate&debug=true"
    )
      .then((res) => res.json())
      .then((feed) => {
        cachedFeedData = feed; // Cache the feed data for helper functions
        updateTable(feed, routeId, table);
      })
      .catch((err) => console.error("Realtime fetch failed", err));
  }

  /**
   * Clears all realtime data from the table and stops the refresh timer
   * @param {HTMLElement} table - The timetable element
   */
  function clearRealtimeData(table) {
    // Clear all realtime cells
    const realtimeCells = table.querySelectorAll("td.realtime");
    realtimeCells.forEach((cell) => {
      cell.textContent = "";
    });

    // Stop the refresh timer
    if (refreshInterval) {
      clearInterval(refreshInterval);
      refreshInterval = null;
    }
  }

  /**
   * Sets up automatic realtime data refresh every 30 seconds
   * @param {string} routeId - The route ID
   * @param {HTMLElement} table - The timetable element
   */
  function followRealtime(routeId, table) {
    // Clear any existing interval
    if (refreshInterval) {
      clearInterval(refreshInterval);
    }

    // Run once right away
    fetchRealtime(routeId, table);

    // Then set up auto-refresh every 30 seconds
    refreshInterval = setInterval(() => {
      fetchRealtime(routeId, table);
    }, 30000);
  }

  /**
   * Updates the timetable with realtime delay information
   * @param {Object} feed - The GTFS-Realtime feed data
   * @param {string} routeId - The route ID to filter trips
   * @param {HTMLElement} table - The timetable element
   */
  function updateTable(feed, routeId, table) {
    // Loop over each stop row in the timetable
    for (const row of table.querySelectorAll("tr")) {
      const stopId = row.getAttribute("data-stop-id");
      const realtimeCell = row.querySelector("td.realtime");
      if (!stopId || !realtimeCell) continue;

      // Get the delay for this stop using the helper function
      const delaySeconds = getStopDelayTime(routeId, stopId);

      // Format and display the delay using the helper function
      realtimeCell.textContent = formatDelayTime(delaySeconds);
    }
  }

  /**
   * Gets the realtime delay for a specific stop on a route
   * @param {string} routeId - The route ID
   * @param {string} stopId - The stop ID
   * @returns {number|null} Delay in seconds, or null if no delay data found
   */
  function getStopDelayTime(routeId, stopId) {
    if (!cachedFeedData) return null;

    // Keep only trips that match this route
    const tripUpdates = cachedFeedData.Entities.filter(
      (e) => e.TripUpdate?.Trip?.RouteId === routeId
    );

    // Get the realtime update for this stop
    for (const trip of tripUpdates) {
      const stopUpdate = trip.TripUpdate?.StopTimeUpdates?.find(
        (u) => u.StopId === stopId
      );
      if (stopUpdate) {
        return stopUpdate.Arrival?.Delay ?? null;
      }
    }

    return null;
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
      return `+${delayMinutes} min`;
    } else {
      return `${delayMinutes} min`;
    }
  }
})();
