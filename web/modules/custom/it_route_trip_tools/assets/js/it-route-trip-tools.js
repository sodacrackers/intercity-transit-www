(function ($, Drupal, drupalSettings) {
	'use strict';
	Drupal.behaviors.it_route_trip_tools = {
		attach(context, settings) {
			if ($('#outbound-tables').length) {
				var outbound_width = $('#outbound-tables').width();
				var outbound_table_width = $('#outbound-tables table thead').width();
				if (outbound_table_width !== undefined) {
					if (outbound_width < outbound_table_width) {
						
					}
				}
			}
			if ($('#inbound-tables').length) {
				var inbound_width = $('#inbound-tables').width();
				var inbound_table_width = $('#inbound-tables table theah').width();
				if (inbound_table_width !== undefined) {
					if (inbound_width < outbound_table_width) {
					}
				}
			}
			once('inbound-forward', '#inbound-forward').forEach(function(element) {
				$(element).on('touch click', function(event){
					event.preventDefault();
					var table = $('#inbound-large-screen-route-table table');
					var leftPos = $(table).scrollLeft();
					$(table).animate({
						scrollLeft: leftPos + 400
					}, 400);
					$('#inbound-back').attr('disabled', false);
				});
			});
			once('inbound-back', '#inbound-back').forEach(function(element) {
				$(element).on('touch click', function(event){
					event.preventDefault();
					var table = $('#inbound-large-screen-route-table table');
					var leftPos = $(table).scrollLeft();
					$(table).animate({
						scrollLeft: leftPos - 400
					}, 400);
					$('#inbound-forward').attr('disabled', false);
				});
			});
			once('outbound-forward', '#outbound-forward').forEach(function(element) {
				$(element).on('touch click', function(event){
					event.preventDefault();
					var table = $('#outbound-large-screen-route-table table');
					var leftPos = $(table).scrollLeft();
					$(table).animate({
						scrollLeft: leftPos + 400
					}, 400);
					$('#outbound-back').attr('disabled', false);
				});
			});
			once('outbound-back', '#outbound-back').forEach(function(element) {
				$(element).on('touch click', function(event){
					event.preventDefault();
					var table = $('#outbound-large-screen-route-table table');
					var leftPos = $(table).scrollLeft();
					$(table).animate({
						scrollLeft: leftPos - 400
					}, 400);
					$('#outbound-forward').attr('disabled', false);
				});
			});
			const $outbound = $('#outbound-large-screen-route-table table');	
			const $inbound = $('#outbound-large-screen-route-table table');	
			let outbound_last_left = $outbound.scrollLeft();
			let inbound_last_left = $inbound.scrollLeft();
			$outbound.on('scroll', function() {
			    const outbound_curr_left = $outbound.scrollLeft();
			    if(outbound_curr_left > outbound_last_left) {
	            	$(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', false);
			   	}
			   	if (outbound_curr_left < outbound_last_left) {
	            	$(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', false);
			   	}
			   	outbound_last_left = outbound_curr_left;
		    });
		    $inbound.on('scroll', function() {
			    const inbound_curr_left = $inbound.scrollLeft();
			    if(inbound_curr_left > inbound_last_left) {
	            	$(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', false);
			   	}
			   	if (inbound_curr_left < inbound_last_left) {
	            	$(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', false);
			   	}
			   	inbound_last_left = inbound_curr_left;
		    });
			$(function () {
		        const $outbound_table = $('#outbound-large-screen-route-table table');
				const $inbound_table = $('#inbound-large-screen-route-table table');
				$outbound_table.on('scroll', function () {
					var new_scroll_left = $outbound_table.scrollLeft(),
		                width = $outbound_table.outerWidth(),
		                scroll_width = $outbound_table.get(0).scrollWidth,
		                right_width_floor = Math.floor(scroll_width - new_scroll_left),
	               		width_floor = Math.floor(width);
		            if (right_width_floor - 1 == width_floor || right_width_floor + 1 == width_floor || right_width_floor == width_floor) {
		            	$(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', true);
		            }
		            if (new_scroll_left === 0) {
		                $(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', true);
		            }
		        });
		        $inbound_table.on('scroll', function () {
		        	var new_scroll_left = $inbound_table.scrollLeft(),
		                width = $inbound_table.outerWidth(),
		                scroll_width = $inbound_table.get(0).scrollWidth,
	               		left_width_check = Math.floor(scroll_width)-Math.floor(new_scroll_left),
	               		width_floor = Math.floor(width);
		            if ((left_width_floor - 1 == width_floor) || (left_width_floor + 1 == width_floor) || (left_width_floor == width_floor)) {
		            	$(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', true);
		            }
		            if (new_scroll_left === 0) {
		                $(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', true);
		            }
		        });
		    });


			$('#edit-routes--2').change(function(){
				var route = $('#edit-routes--2').val();
				var cur_action = $('form#routes-form--2').prop('action');
				$('form#routes-form--2').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
			});
			$('#edit-routes').change(function(){
				var route = $('#edit-routes').val();
				var cur_action = $('form#routes-form').prop('action');
				$('form#routes-form').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
			});
			once('select-edit-stop', 'select#edit-stop').forEach(function(element) {
				$(element).change(function(){
					var route = $('#edit-stop').val();
					var cur_action = $('form#stops-form').prop('action');
					$('form#stops-form').attr('action', settings.it_route_trip_tools.stops_action_path + '/' + route);
				});
			});
			once('input-stop', 'input#stop').forEach(function(element) {
				$(element).click(function() {
					$('input#stop').toggleClass('active');
					$('input#stop').toggleClass('timepoints-hide');
					$('input#stop').toggleClass('timepoints-hide');
					$('#outbound-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
					$('#inbound-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
					$('#outbound-small-screen-route-table table').find('.hide-stop').toggleClass('hidden');
					$('#inbound-small-screen-route-table table').find('.hide-stop').toggleClass('hidden');
				});
			});
			once('button-direction', 'button#direction').forEach(function(element) {
				$(element).click(function() {
					$('button#direction').toggleClass('inbound');
					$('button#direction').toggleClass('outbound');
					$('.dir-heading').toggleClass('hidden');
					$('.dir-tables').toggleClass('hidden');
					$('.map-frame .maps').toggleClass('hidden').toggleClass('show-dir').toggleClass('hide-dir');
					if ($('#outbound-tables').length) {
						var outbound_width = $('#outbound-tables').width();
						var outbound_table_width = $('#outbound-tables table thead').width();
					}
					if ($('#inbound-tables').length) {
						var inbound_width = $('#inbound-tables').width();
						var inbound_table_width = $('#inbound-tables table theah').width();
					}
				});
			});
			$('.outbound-panel').on('hide.bs.collapse', function () {
				$('.outbound-route-map-toggle').html('Show Route Map');
			})
			$('.outbound-panel').on('show.bs.collapse', function () {				
				$('.outbound-route-map-toggle').html('Hide Route Map');
				if (!$(this).hasClass('already-opened')) {
					initMap_outbound();
					$(this).addClass('already-opened');
				}
			})
			$('.inbound-panel').on('hide.bs.collapse', function () {
				$('.inbound-route-map-toggle').html('Show Route Map');
			})
			$('.inbound-panel').on('show.bs.collapse', function () {
				$('.inbound-route-map-toggle').html('Hide Route Map');
				if (!$(this).hasClass('already-opened')) {
					initMap_inbound();
					$(this).addClass('already-opened');
				}
			})
			$('.stops-panel').on('hide.bs.collapse', function () {
				$('.stops-map-toggle').html('Show Stops Map');
			});
			
			$('.stops-panel').on('show.bs.collapse', function () {				
				$('.stops-map-toggle').html('Hide Stops Map');
				if (!$(this).hasClass('already-opened')) {
					$(this).addClass('already-opened');
				}
			})
			if ($(window).width() < 992) {
				$('.large-screen-route-table').attr('aria-hidden','true');
				$('.small-screen-route-table').attr('aria-hidden','false');
			}
			else {
				$('.large-screen-route-table').attr('aria-hidden','false');
				$('.small-screen-route-table').attr('aria-hidden','true');
			}
			$( window ).resize(function() {
				if ($(window).width() < 992) {
					$('.large-screen-route-table').attr('aria-hidden','true');
					$('.small-screen-route-table').attr('aria-hidden','false');
				}
				else {
					$('.large-screen-route-table').attr('aria-hidden','false');
					$('.small-screen-route-table').attr('aria-hidden','true');
				}
			});

		}
	};

})(jQuery, Drupal, drupalSettings, document, window);
