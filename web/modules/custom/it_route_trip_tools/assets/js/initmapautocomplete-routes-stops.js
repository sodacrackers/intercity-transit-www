(function ($, Drupal, drupalSettings) {
	'use strict';
	Drupal.behaviors.route_stop_autocomplete = {
		attach(context, settings) {
			//This initMapAutoCcomplete is used for the filtered routes and stops pages.
			//Have to use this since we are already calling another map on these pages.
			initMapAutocomplete();
		}
	};

})(jQuery, Drupal, drupalSettings, document, window);