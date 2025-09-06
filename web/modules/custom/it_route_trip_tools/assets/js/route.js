(function ($, Drupal) {
  'use strict';
  let has_loaded= false;
  Drupal.behaviors.route_tabs = {
    attach(context, settings) {
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