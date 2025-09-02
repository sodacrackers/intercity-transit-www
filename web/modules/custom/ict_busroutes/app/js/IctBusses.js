/**
 * IctBusses Module
 *
 * Provides utility functions for the Intercity Transit routes application.
 * Handles loading states, logging, and common UI interactions.
 *
 * @namespace IctBusses
 */
export const IctBusses = {
  /**
   * Shows the loading spinner by removing the 'hidden' CSS class
   *
   * @method showSpinner
   * @description Displays a loading spinner to indicate background processing
   */
  showSpinner: function () {
    var el = document.querySelector(".loading-spinner");
    if (!el) return;
    el.classList.remove("hidden");
  },

  /**
   * Hides the loading spinner by adding the 'hidden' CSS class
   *
   * @method hideSpinner
   * @description Hides the loading spinner when processing is complete
   */
  hideSpinner: function () {
    var el = document.querySelector(".loading-spinner");
    if (!el) return;
    el.classList.add("hidden");
  },

  /**
   * Gets the name of the calling function from the call stack
   *
   * @method getCallerName
   * @returns {string} The name of the calling function or 'unknown'
   * @private
   */
  getCallerName: function () {
    try {
      const stack = new Error().stack;
      if (!stack) return "unknown";

      const lines = stack.split("\n");
      // Skip: Error line, this function, and the log function
      const callerLine = lines[3];

      if (!callerLine) return "unknown";

      // Extract function name from different stack formats
      // Look for patterns like "at Object.methodName" or "at ModuleName.methodName"
      let match = callerLine.match(/at\s+([^.\s]+)\.([^.\s\(]+)/);
      if (match && match[1] && match[2]) {
        const moduleName = match[1] === 'Object' ? 'Module' : match[1];
        return `${moduleName}.${match[2]}`;
      }

      // Fallback to original pattern for simple function names
      match = callerLine.match(/at\s+([^.\s]+)/);
      return match && match[1] !== "Object" ? match[1] : "anonymous";
    } catch (e) {
      return "unknown";
    }
  },

  /**
   * Logs informational messages to the browser console
   *
   * @method log
   * @param {string} msg - The message to log
   * @param {*} [extra] - Optional additional data to log
   * @description Outputs prefixed log messages for debugging and monitoring
   */
  log: function (msg, extra) {
    if (!console || !console.log) return;
    const caller = this.getCallerName();
    console.log(`[IctBusses:${caller}]`, msg, extra);
  },

  /**
   * Logs error messages to the browser console
   *
   * @method logError
   * @param {string} msg - The error message to log
   * @param {*} [extra] - Optional additional data to log
   * @description Outputs prefixed error messages to the console
   */
  logError: function (msg, extra) {
    if (!console || !console.error) return;
    const caller = this.getCallerName();
    console.error(`[IctBusses:${caller}]`, msg, extra);
  },

  /**
   * Logs warning messages to the browser console
   *
   * @method logWarning
   * @param {string} msg - The warning message to log
   * @param {*} [extra] - Optional additional data to log
   * @description Outputs prefixed warning messages to the console
   */
  logWarning: function (msg, extra) {
    if (!console || !console.warn) return;
    const caller = this.getCallerName();
    console.warn(`[IctBusses:${caller}]`, msg, extra);
  },
};
