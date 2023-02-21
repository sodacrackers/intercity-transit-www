
(function ($, Drupal, drupalSettings) {
	'use strict';
	Drupal.behaviors.search_form_control = {
		attach(context, settings) {
			$('button.search-toggle').once().click(function(){
				//$(this).next('form').slideDown();
			});
		}
	};

})(jQuery, Drupal, drupalSettings, document, window);