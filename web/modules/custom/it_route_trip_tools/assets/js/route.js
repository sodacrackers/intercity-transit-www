(function ($, Drupal) {
  'use strict';
  let has_loaded= false;
  Drupal.behaviors.route_tabs = {
    attach(context, settings) {
      if (!has_loaded) {
        let scriptEle = document.createElement("script");

        scriptEle.setAttribute("src", '/modules/custom/ict_routes_react_app/js/dist/main.min.js');
        scriptEle.setAttribute("type", "text/javascript");
        scriptEle.setAttribute("async", true);

        document.body.appendChild(scriptEle);
        has_loaded = true;
      }
      $('#real-time-container').hide();
      once('route-tab', '#realtime-tab', context).forEach(function (element) {
        $(element).click(function () {
          $('#schedule').hide();
          $('#real-time-container').show();
        });
      });
      once('schedule-tab', '#schedule-tab', context).forEach(function (element) {
        $(element).click(function () {
          $('#schedule').show();
          $('#real-time-container').hide();
        });
      });
    }
  };

})(jQuery, Drupal);