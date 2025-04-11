(function ($, Drupal, window) {
	Drupal.behaviors.helpers = {
		attach: function (context, settings) {
    		'use strict';
//
//     		var today = $('#today').attr('data-date');
//    		$('.fc-listYear-button').click(function() {
//    			$('.fc-listYear-view').hide();
//    			$('tr.fc-list-heading').each(function() {
//    				var eventDate = $(this).attr('data-date');
//    				var newEventDate = eventDate.replace(/-/g, '');
//    				if(newEventDate < today) {
//      					$(this).hide(function() {
//							$(this).nextUntil('tr.fc-list-heading').hide();
//						});
//    				}
//      			}).promise().done( function() {
//  						$('.fc-listYear-view').show().promise().done( function() {
//  								$('.fc-scroller').css('height', 'auto');
//							});
//					});
//      		});
			$('.fc-right .fc-button-group button.fc-button:last-of-type').removeClass('fc-corner-right');
			$('.fc-right .fc-button-group').once().append('<button type="button" class="agendaList-button fc-button fc-state-default fc-corner-right">list</button>');
			$('.agendaList-button').hover(function() {
				$(this).toggleClass('fc-state-hover');
			});
			$('.agendaList-button').click(function() {
				$('.fc-right .fc-button').removeClass('fc-state-active');
				$('.agendaList-button').addClass('fc-state-active');
				if (!$('#calendar-list').hasClass('show')) {
					$('.fc-view-container').fadeOut().promise().done( function() {
						$('.fc-center').hide();
						$('#calendar-list').fadeIn(100).promise().done( function() {
							$(this).removeClass('hidden');
							$(this).addClass('show');
						});
					});
				};
			});
			$('.fc-right .fc-button').not('.agendaList-button').click(function() {
				if ($('#calendar-list').hasClass('show')) {
					$('.agendaList-button').removeClass('fc-state-active');
					$('#calendar-list').fadeOut().promise().done( function() {
						$('.fc-center').show();
						$(this).removeClass('show');
						$(this).addClass('hidden');
						$('.fc-view-container').fadeIn(100);
					});
				};
			});
    	}
  	};
})(jQuery, Drupal, window);