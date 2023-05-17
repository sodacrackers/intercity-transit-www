(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.route_tabs = {
    attach(context, settings) {
      $('#realtime-tab', context).once('route-tab').click(function () {
        $('#schedule').hide();
        let scriptEle = document.createElement("script");

        scriptEle.setAttribute("src", '/modules/custom/ict_routes_react_app/js/dist/main.min.js');
        scriptEle.setAttribute("type", "text/javascript");
        scriptEle.setAttribute("async", true);

        document.body.appendChild(scriptEle);
      })
    }
  };

})(jQuery, Drupal, drupalSettings, document, window);