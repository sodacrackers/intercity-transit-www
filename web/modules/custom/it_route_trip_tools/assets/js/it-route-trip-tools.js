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

      $('.inbound-weekdays-forward').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.inbound-weekdays-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos + 400
        }, 400);
        $('.inbound-weekdays-back').attr('disabled', false);
      });
      $('.inbound-weekdays-back').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.inbound-weekdays-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos - 400
        }, 400);
        $('.inbound-weekdays-forward').attr('disabled', false);
      });
      $('.outbound-weekdays-forward').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.outbound-weekdays-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos + 400
        }, 400);
        $('.outbound-weekdays-back').attr('disabled', false);
      });
      $('.outbound-weekdays-back').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.outbound-weekdays-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos - 400
        }, 400);
        $('.outbound-weekdays-forward').attr('disabled', false);
      });


      $('.inbound-weekends-forward').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.inbound-weekends-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos + 400
        }, 400);
        $('.inbound-weekends-back').attr('disabled', false);
      });
      $('.inbound-weekends-back').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.inbound-weekends-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos - 400
        }, 400);
        $('.inbound-weekends-forward').attr('disabled', false);
      });
      $('.outbound-weekends-forward').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.outbound-weekends-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos + 400
        }, 400);
        $('.outbound-weekends-back').attr('disabled', false);
      });
      $('.outbound-weekends-back').once().on('touch, click', function () {
        event.preventDefault();
        var table = $('.outbound-weekends-large-screen-route-table table');
        var leftPos = $(table).scrollLeft();
        $(table).animate({
          scrollLeft: leftPos - 400
        }, 400);
        $('.outbound-weekends-forward').attr('disabled', false);
      });
      // const $outbound = $('#outbound-large-screen-route-table table');
      // const $inbound = $('#inbound-large-screen-route-table table');
      // let outbound_last_left = $outbound.scrollLeft();
      // let inbound_last_left = $inbound.scrollLeft();
      // $outbound.on('scroll', function() {
      // 	const outbound_curr_left = $outbound.scrollLeft();
      // 	if(outbound_curr_left > outbound_last_left) {
      // 		$(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled',
      // false); } if (outbound_curr_left < outbound_last_left) {
      // $(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled',
      // false); } outbound_last_left = outbound_curr_left; });
      // $inbound.on('scroll', function() { const inbound_curr_left =
      // $inbound.scrollLeft(); if(inbound_curr_left > inbound_last_left) {
      // $(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled',
      // false); } if (inbound_curr_left < inbound_last_left) {
      // $(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled',
      // false); } inbound_last_left = inbound_curr_left; }); $(function () {
      // const $outbound_table = $('#outbound-large-screen-route-table table'); const $inbound_table = $('#inbound-large-screen-route-table table'); $outbound_table.on('scroll', function () { var new_scroll_left = $outbound_table.scrollLeft(), width = $outbound_table.outerWidth(), scroll_width = $outbound_table.get(0).scrollWidth, right_width_floor = Math.floor(scroll_width - new_scroll_left), width_floor = Math.floor(width); if (right_width_floor - 1 == width_floor || right_width_floor + 1 == width_floor || right_width_floor == width_floor) { $(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', true); } if (new_scroll_left === 0) { $(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', true); } }); $inbound_table.on('scroll', function () { var new_scroll_left = $inbound_table.scrollLeft(), width = $inbound_table.outerWidth(), scroll_width = $inbound_table.get(0).scrollWidth, left_width_check = Math.floor(scroll_width)-Math.floor(new_scroll_left), width_floor = Math.floor(width); if ((left_width_floor - 1 == width_floor) || (left_width_floor + 1 == width_floor) || (left_width_floor == width_floor)) { $(this).parent().prev('.table-navigation-buttons').find('.right-table-button').attr('disabled', true); } if (new_scroll_left === 0) { $(this).parent().prev('.table-navigation-buttons').find('.left-table-button').attr('disabled', true); } }); });


      $('#edit-routes--2').change(function () {
        var route = $('#edit-routes--2').val();
        var cur_action = $('form#routes-form--2').prop('action');
        $('form#routes-form--2').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
      });
      $('#edit-routes').change(function () {
        var route = $('#edit-routes').val();
        var cur_action = $('form#routes-form').prop('action');
        $('form#routes-form').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
      });
      $('select#edit-stop').once().change(function () {
        var route = $('#edit-stop').val();
        var cur_action = $('form#stops-form').prop('action');
        $('form#stops-form').attr('action', settings.it_route_trip_tools.stops_action_path + '/' + route);
      });
      $('input#stop').once().click(function () {
        $('input#stop').toggleClass('active');
        $('input#stop').toggleClass('timepoints-hide');
        $('input#stop').toggleClass('timepoints-hide');
        $('#outbound-weekdays-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
        $('#inbound-weekdays-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
        $('#outbound-weekends-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
        $('#inbound-weekends-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
      });
      $('button#direction').once().click(function () {
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
      $('input[name="direction"]').once().click(function () {
        const choice = $(this).val();
        const opposite = choice === 'inbound' ? 'outbound' : 'inbound';
        $('button#direction').addClass(choice);
        $('button#direction').removeClass(opposite);
        $('.dir-heading-' + choice).removeClass('hidden');
        $('.dir-heading-' + opposite).addClass('hidden');
        $('.customize-view-' + choice).removeClass('hidden');
        $('.customize-view-' + opposite).addClass('hidden');
        $('.dir-tables-' + choice).removeClass('hidden');
        $('.dir-tables-' + opposite).addClass('hidden');
        $('.dir-lists-' + choice).removeClass('hidden');
        $('.dir-lists-' + opposite).addClass('hidden');
        $('.dir-lists-' + choice).removeClass('show-dir');
        $('.dir-lists-' + opposite).addClass('show-dir');
        $('.map-frame-' + choice + ' .maps-' + choice).removeClass('hidden').removeClass('show-dir').removeClass('hide-dir');
        $('.map-frame-' + opposite + ' .maps-' + opposite).addClass('hidden').addClass('show-dir').addClass('hide-dir');
        if (choice === 'inbound') {
          if ($('input[name="dayoftravel"]:checked').val() === 'weekdays-info') {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').removeClass('hide');
          }
          else {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').removeClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
        }
        else {
          if ($('input[name="dayoftravel"]:checked').val() === 'weekdays-info') {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').removeClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
          else {
            $('#route-map-weekend #outbound-weekends-map').removeClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
        }
      });
      $('input[name="dayoftravel"]').once().click(function () {
        const choice = $(this).val();
        const opposite = choice === 'weekdays-info' ? 'weekend-info' : 'weekdays-info';
        $('.' + choice).removeClass('hide');
        $('.' + opposite).addClass('hide');
        if (choice === 'weekdays-info') {
          if ($('input[name="direction"]:checked').val() === 'inbound') {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').removeClass('hide');
          }
          else {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').removeClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
        }
        else {
          if ($('input[name="direction"]:checked').val() === 'inbound') {
            $('#route-map-weekend #outbound-weekends-map').addClass('hide');
            $('#route-map-weekend #inbound-weekends-map').removeClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
          else {
            $('#route-map-weekend #outbound-weekends-map').removeClass('hide');
            $('#route-map-weekend #inbound-weekends-map').addClass('hide');
            $('#route-map-weekdays #outbound-weekdays-map').addClass('hide');
            $('#route-map-weekdays #inbound-weekdays-map').addClass('hide');
          }
        }
      });
      $('.btn-stops-toggle').once().click(function () {
        $(this).find('.btn').toggleClass('active');
        $(this).find('.btn').toggleClass('btn-primary');
        $(this).find('.btn').toggleClass('btn-default');
        $('.btn-stops-toggle').not($(this)).each(function () {
          $(this).find('.btn').toggleClass('active');
          $(this).find('.btn').toggleClass('btn-primary');
          $(this).find('.btn').toggleClass('btn-default');
        });
      });
      $('input[name="display_stops_options"]').once().change(function () {
        const show_rows = $(this).val();
        if (show_rows === 'allstops') {
          $('tr.non-timepoint').show();
          $('.lists-list-item.not-timepoint').show();
        }
        else {
          $('tr.non-timepoint').hide();
          $('.lists-list-item.not-timepoint').hide();
        }
      });
      $('#applyRoutesFilter').once().click(function () {
        let stopList = '';
        const searchIDs = $('input[name="show_stop"]:checked').map(function () {
          const check_val = $(this).val();
          stopList = stopList + $('label[for="checkstop' + check_val + '"]').html() + ', ';
          return check_val;
        }).get();
        if (searchIDs.length === 0) {
          $('tr[data-stop-id]').show();
          $('div[data-stop-id]').show();
          $('#dropdownMenuButton').html('Select all that apply');
          $('.dropdown .ui-dialog-titlebar-close').hide();
        }
        else {
          $('tr[data-stop-id]').hide();
          $('div[data-stop-id]').hide();
          $('.dropdown .ui-dialog-titlebar-close').show();
          $('#dropdownMenuButton').html(stopList.substr(0, 25) + '...');
          searchIDs.forEach(function (item) {
            $('tr[data-stop-id="' + item + '"]').show();
            $('div[data-stop-id="' + item + '"]').show();
          });
        }
        if (searchIDs.length === 1) {
          $('.transit-footer').removeClass('transit-margin');
          $('.transit-footer').addClass('transit-margin-0');
        }
        else {
          $('.transit-footer').addClass('transit-margin');
          $('.transit-footer').removeClass('transit-margin-0');
        }
      });
      $('.dropdown .ui-dialog-titlebar-close').once().click(function () {
        $('input[name="show_stop"]:checked').map(function () {
          $(this).prop('checked', false);
        }).get();
        $('tr[data-stop-id]').show();
        $('div[data-stop-id]').show();
        $('#dropdownMenuButton').html('Select all that apply');
        $('.dropdown .ui-dialog-titlebar-close').hide();
        $(this).hide();
      });
      $('input[name="viewmode"]').once().change(function () {
        const viewmode = $(this).val();
        if (viewmode === 'viewtable') {
          $('.mobile-lists-container').hide();
          $('.mobile-tables-container').show();
        }
        else {
          $('.mobile-lists-container').show();
          $('.mobile-lists-container').removeClass('hide-on-mobile');
          $('.mobile-tables-container').hide();
        }
      });
      $('select#route-change').once().change(function () {
        window.location.href = '/plan-your-trip/routes/' + $(this).val();
      });
      $('.outbound-weekdays-panel').once().on('click', function () {
        let currentTitle = $('.outbound-weekdays-route-map-toggle').html();
        $('.outbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
        $('#outbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
        if (!$(this).hasClass('already-opened')) {
          initMap_outbound_weekdays();
          $(this).addClass('already-opened');
        }
        $('.outbound-weekends-panel').each(function () {
          let currentTitle = $('.outbound-weekends-route-map-toggle').html();
          $('.outbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekends();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekdays-panel').each(function () {
          let currentTitle = $('.inbound-weekdays-route-map-toggle').html();
          $('.inbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekends-panel').each(function () {
          let currentTitle = $('.inbound-weekends-route-map-toggle').html();
          $('.inbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekends();
            $(this).addClass('already-opened');
          }
        });
      });
      $('.outbound-weekends-panel').once().on('click', function () {
        let currentTitle = $('.outbound-weekends-route-map-toggle').html();
        $('.outbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
        $('#outbound-weekends-map-body').toggleClass('hide').toggleClass('show');
        if (!$(this).hasClass('already-opened')) {
          initMap_outbound_weekends();
          $(this).addClass('already-opened');
        }
        $('.outbound-weekdays-panel').each(function () {
          let currentTitle = $('.outbound-weekdays-route-map-toggle').html();
          $('.outbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekdays-panel').each(function () {
          let currentTitle = $('.inbound-weekdays-route-map-toggle').html();
          $('.inbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekends-panel').each(function () {
          let currentTitle = $('.inbound-weekends-route-map-toggle').html();
          $('.inbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekends();
            $(this).addClass('already-opened');
          }
        });
      });
      $('.inbound-weekdays-panel').once().on('click', function () {
        let currentTitle = $('.inbound-weekdays-route-map-toggle').html();
        $('.inbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
        $('#inbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
        if (!$(this).hasClass('already-opened')) {
          initMap_inbound_weekdays();
          $(this).addClass('already-opened');
        }
        $('.outbound-weekdays-panel').each(function () {
          let currentTitle = $('.outbound-weekdays-route-map-toggle').html();
          $('.outbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
        $('.outbound-weekends-panel').each(function () {
          let currentTitle = $('.outbound-weekends-route-map-toggle').html();
          $('.outbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekends();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekends-panel').each(function () {
          let currentTitle = $('.inbound-weekends-route-map-toggle').html();
          $('.inbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekends();
            $(this).addClass('already-opened');
          }
        });
      });
      $('.inbound-weekends-panel').once().on('click', function () {
        let currentTitle = $('.inbound-weekends-route-map-toggle').html();
        $('.inbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
        $('#inbound-weekends-map-body').toggleClass('hide').toggleClass('show');
        if (!$(this).hasClass('already-opened')) {
          initMap_inbound_weekends();
          $(this).addClass('already-opened');
        }
        $('.outbound-weekdays-panel').each(function () {
          let currentTitle = $('.outbound-weekdays-route-map-toggle').html();
          $('.outbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
        $('.outbound-weekends-panel').each(function () {
          let currentTitle = $('.outbound-weekends-route-map-toggle').html();
          $('.outbound-weekends-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-weekends-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound_weekends();
            $(this).addClass('already-opened');
          }
        });
        $('.inbound-weekdays-panel').each(function () {
          let currentTitle = $('.inbound-weekdays-route-map-toggle').html();
          $('.inbound-weekdays-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-weekdays-map-body').toggleClass('hide').toggleClass('show');
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound_weekdays();
            $(this).addClass('already-opened');
          }
        });
      });

      $('.panel-title .glyphicon').once('bindclick').on('click', function (event) {
        $(event.target).closest('.outbound-weekend-panel').find('a[data-toggle]').click();
        $(event.target).closest('.outbound-weekdays-panel').find('a[data-toggle]').click();
        $(event.target).closest('.inbound-weekend-panel').find('a[data-toggle]').click();
        $(event.target).closest('.inbound-weekdays-panel').find('a[data-toggle]').click();
      });

      $('.inbound-panel').once().on('show.bs.collapse', function () {
        $('.inbound-route-map-toggle').html('Hide Map');
        if (!$(this).hasClass('already-opened')) {
          // initMap_inbound();
          $(this).addClass('already-opened');
        }
      })
      $('.stops-panel').once().on('hide.bs.collapse', function () {
        $('.stops-map-toggle').html('Show Stops Map');
      });

      $('.stops-panel').once().on('show.bs.collapse', function () {
        $('.stops-map-toggle').html('Hide Stops Map');
        if (!$(this).hasClass('already-opened')) {
          $(this).addClass('already-opened');
        }
      })
      if ($(window).width() < 992) {
        $('.large-screen-route-table').attr('aria-hidden', 'true');
        $('.small-screen-route-table').attr('aria-hidden', 'false');
      }
      else {
        $('.large-screen-route-table').attr('aria-hidden', 'false');
        $('.small-screen-route-table').attr('aria-hidden', 'true');
      }
      $(window).resize(function () {
        if ($(window).width() < 992) {
          $('.large-screen-route-table').attr('aria-hidden', 'true');
          $('.small-screen-route-table').attr('aria-hidden', 'false');
        }
        else {
          $('.large-screen-route-table').attr('aria-hidden', 'false');
          $('.small-screen-route-table').attr('aria-hidden', 'true');
        }
      });

    }
  };

})(jQuery, Drupal, drupalSettings, document, window);