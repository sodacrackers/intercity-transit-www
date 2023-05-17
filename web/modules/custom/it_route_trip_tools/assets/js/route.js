(function ($, Drupal, drupalSettings) {
  'use strict';
  let has_loaded= false;
  Drupal.behaviors.route_tabs = {
    attach(context, settings) {
      $('#real-time-container').hide();
      $('#realtime-tab', context).once('route-tab').click(function () {
        $('#schedule').hide();
        $('#real-time-container').show();
        if (!has_loaded) {
          let scriptEle = document.createElement("script");

          scriptEle.setAttribute("src", '/modules/custom/ict_routes_react_app/js/dist/main.min.js');
          scriptEle.setAttribute("type", "text/javascript");
          scriptEle.setAttribute("async", true);

          document.body.appendChild(scriptEle);
          has_loaded = true;
        }

      });
      $('#schedule-tab', context).once('schedule-tab').click(function () {
        $('#schedule').show();
        $('#real-time-container').hide();
      });
    }
  };

})(jQuery, Drupal, drupalSettings, document, window);