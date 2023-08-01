(function ($, Drupal) {

  "use strict";

  /**
   * Attach behaviors to links for entities.
   */
  Drupal.behaviors.dateDefaultMin = {
    attach: function (context) {
      $('.datetime-min-max-default-min', context).once('datetime-min-max-default-min').each(function () {
        this.addEventListener("focus", function () {
          if (this.min && !this.value) {
            this.value = this.min;
          }
        });
      });
    }
  };
})(jQuery, Drupal);
