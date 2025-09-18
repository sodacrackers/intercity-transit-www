(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.it_route_trip_tools = {
    attach(context, settings) {
      var is_resetting = false;
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

      // Helper function to check scroll boundaries and update button states
      function updateScrollButtonStates(table, backButton, forwardButton) {
        var scrollLeft = $(table).scrollLeft();
        var scrollWidth = $(table).get(0).scrollWidth;
        var clientWidth = $(table).get(0).clientWidth;
        
        // Check if at the beginning
        if (scrollLeft <= 0) {
          $(backButton).attr('disabled', true);
        } else {
          $(backButton).attr('disabled', false);
        }
        
        // Check if at the end (with small tolerance for rounding)
        if (scrollLeft + clientWidth >= scrollWidth - 1) {
          $(forwardButton).attr('disabled', true);
        } else {
          $(forwardButton).attr('disabled', false);
        }
      }

      once('inbound-forward', '.inbound-forward').forEach(function (element) {
        $(element).on('touch, click', function () {
          event.preventDefault();
          var table = $('.inbound-large-screen-route-table table');
          var leftPos = $(table).scrollLeft();
          $(table).animate({
            scrollLeft: leftPos + 400
          }, 400, function() {
            // Update button states after animation completes
            updateScrollButtonStates(table, '.inbound-back', '.inbound-forward');
          });
        });
      });
      once('inbound-back', '.inbound-back').forEach(function (element) {
        $(element).on('touch, click', function () {
          event.preventDefault();
          var table = $('.inbound-large-screen-route-table table');
          var leftPos = $(table).scrollLeft();
          $(table).animate({
            scrollLeft: leftPos - 400
          }, 400, function() {
            // Update button states after animation completes
            updateScrollButtonStates(table, '.inbound-back', '.inbound-forward');
          });
        });
      });
      once('outbound-forward', '.outbound-forward').forEach(function (element) {
        $(element).on('touch, click', function () {
          event.preventDefault();
          var table = $('.outbound-large-screen-route-table table');
          var leftPos = $(table).scrollLeft();
          $(table).animate({
            scrollLeft: leftPos + 400
          }, 400, function() {
            // Update button states after animation completes
            updateScrollButtonStates(table, '.outbound-back', '.outbound-forward');
          });
        });
      });

      once('outbound-back', '.outbound-back').forEach(function (element) {
        $(element).on('touch, click', function () {
          event.preventDefault();
          var table = $('.outbound-large-screen-route-table table');
          var leftPos = $(table).scrollLeft();
          $(table).animate({
            scrollLeft: leftPos - 400
          }, 400, function() {
            // Update button states after animation completes
            updateScrollButtonStates(table, '.outbound-back', '.outbound-forward');
          });
        });
      });

      once('service-option-datepicker', '#edit-service-option--2, #edit-service-option, #edit-stop-date', context).forEach(function (element) {
        const url = new URL(window.location.href);
        const currentDefaultDate = url.searchParams.has('date')
          ? (() => {
              const dateStr = url.searchParams.get('date');
              // Expecting format YYYY-MM-DD
              const parts = dateStr.split('-');
              if (parts.length === 3) {
                // Month is 0-based in JS Date
                return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
              }
              return new Date();
            })()
          : new Date();
        $(element).datepicker({
          dateFormat: 'mm/dd/yy',
          defaultDate: currentDefaultDate,
          beforeShowDay: function (date) {
            const availableDays = drupalSettings.it_route_trip_tools.available_days;
            const thisDate = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0');
            if (availableDays.hasOwnProperty(thisDate)) {
                return [true, "","Service"];
            } else {
                return [false,"","No Service"];
            }
          },
        });
        $(element).datepicker('setDate', currentDefaultDate);
      });

      $('#edit-routes--2,#edit-service-option--2').change(function () {
        var route = $('#edit-routes--2').val();
        $('form#routes-form--2').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
      });

      $('#edit-routes,#edit-service-option').change(function () {
        var route = $('#edit-routes').val();
        $('form#routes-form').attr('action', settings.it_route_trip_tools.routes_action_path + '/' + route);
      });

      once('select-edit-stop', 'select#edit-stop, input#edit-stop-date').forEach(function (element) {
        $(element).change(function () {
          var route = $('#edit-stop').val();
          $('form#stops-form').attr('action', settings.it_route_trip_tools.stops_action_path + '/' + route);
        });
      });

      once('input-stop', 'input#stop').forEach(function (element) {
        $(element).click(function () {
          $('input#stop').toggleClass('active');
          $('input#stop').toggleClass('timepoints-hide');
          $('input#stop').toggleClass('timepoints-hide');
          $('#outbound-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
          $('#inbound-large-screen-route-table table').find('.hide-stop').toggleClass('hidden');
        });
      });

      once('button-direction', 'button#direction').forEach(function (element) {
        $(element).click(function () {
          is_resetting = true;
          $('input[name="show_stop"]').prop('checked', false);
          $('#applyRoutesFilter').click();
          is_resetting = false;
          $('button#direction').toggleClass('inbound');
          $('button#direction').toggleClass('outbound');
          $('.dir-heading').toggleClass('hidden');
          $('.dir-tables').toggleClass('hidden');
          $('.map-frame .maps').toggleClass('hidden').toggleClass('show-dir').toggleClass('hide-dir');
        });
      });

      once('input-name-direction', 'input[name="direction"]').forEach(function (element) {
        $(element).click(function () {
          is_resetting = true;
          $('input[name="show_stop"]').prop('checked', false);
          $('#applyRoutesFilter').click();
          is_resetting = false;
          if (!$('input[name="display_stops_options"][value="allstops"]').is(':checked')) {
            
            $('input[name="display_stops_options"][value="allstops"]').prop('checked', true);
            $('input[name="display_stops_options"][value="timepoint"]').prop('checked', true);
            $('input[name="display_stops_options"][value="allstops"]').parent().addClass('active focus btn-default').removeClass('btn-primary');
            $('input[name="display_stops_options"][value="timepoint"]').parent().removeClass('active focus btn-default').addClass('btn-primary');
          }
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
            $('#route-map #outbound-map').addClass('hide');
            $('#route-map #inbound-map').removeClass('hide');
          }
          else {
            $('#route-map #outbound-map').removeClass('hide');
            $('#route-map #inbound-map').addClass('hide');
          }
        });
      });

      once('input-name-dayoftravel-date', 'input[name="dayoftravel-date"]').forEach(function (element) {
        const url = new URL(window.location.href);
        const currentDefaultDate = url.searchParams.has('date')
          ? (() => {
              const dateStr = url.searchParams.get('date');
              // Expecting format YYYY-MM-DD
              const parts = dateStr.split('-');
              if (parts.length === 3) {
                // Month is 0-based in JS Date
                return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
              }
              return new Date();
            })()
          : new Date();
        $(element).datepicker({
          dateFormat: 'mm/dd/yy',
          defaultDate: currentDefaultDate,
          beforeShowDay: function (date) {
            const availableDays = drupalSettings.it_route_trip_tools.available_days;
            const thisDate = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0');
            if (availableDays.hasOwnProperty(thisDate)) {
                return [true, "","Service"];
            } else {
                return [false,"","No Service"];
            }
          },
          onSelect: function (dateText, inst) {
            // Handle the date selection
            const url = new URL(window.location.href);
            const dateParts = dateText.split('/');
            const month = dateParts[0];
            const day = dateParts[1];
            const year = dateParts[2];
            url.searchParams.set('date', year + '-' + month + '-' + day);
            window.location.href = url.toString();
          }
        });
        $(element).datepicker('setDate', currentDefaultDate);
      });

      once('btn-stops-toggle', '.btn-stops-toggle').forEach(function (element) {
        $(element).click(function () {
          $(this).find('.btn').toggleClass('active');
          $(this).find('.btn').toggleClass('btn-primary');
          $(this).find('.btn').toggleClass('btn-default');
          $('.btn-stops-toggle').not($(this)).each(function () {
            $(this).find('.btn').toggleClass('active');
            $(this).find('.btn').toggleClass('btn-primary');
            $(this).find('.btn').toggleClass('btn-default');
          });
        });
      });

      once('input-name-display_stops_options', 'input[name="display_stops_options"]').forEach(function (element) {
        $(element).change(function () {
          is_resetting = true;
          $('input[name="show_stop"]').prop('checked', false);
          $('#applyRoutesFilter').click();
          is_resetting = false;
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
      });

      once('applyRoutesFilter', '#applyRoutesFilter').forEach(function (element) {
        $(element).click(function () {
          if (!is_resetting) {
            is_resetting = true;
            console.log($('input[name="display_stops_options"][value="allstops"]').is(':checked'));
            if (!$('input[name="display_stops_options"][value="allstops"]').is(':checked')) {
              
              $('input[name="display_stops_options"][value="allstops"]').prop('checked', true);
              $('input[name="display_stops_options"][value="timepoint"]').prop('checked', true);
              $('input[name="display_stops_options"][value="allstops"]').parent().addClass('active focus btn-default').removeClass('btn-primary');
              $('input[name="display_stops_options"][value="timepoint"]').parent().removeClass('active focus btn-default').addClass('btn-primary');
            }
            is_resetting = false;
          }
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
      });

      once('dropdown-ui-dialog-titlebar-close', '.dropdown .ui-dialog-titlebar-close').forEach(function (element) {
        $(element).click(function () {
          $('input[name="show_stop"]:checked').map(function () {
            $(this).prop('checked', false);
          }).get();
          $('tr[data-stop-id]').show();
          $('div[data-stop-id]').show();
          $('#dropdownMenuButton').html('Select all that apply');
          $('.dropdown .ui-dialog-titlebar-close').hide();
          $(this).hide();
        });
      });

      once('input-name-viewmode', 'input[name="viewmode"]').forEach(function (element) {
        $(element).change(function () {
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
      });

      once('select-route-change', 'select#route-change').forEach(function (element) {
        $(element).change(function () {
          window.location.href = '/plan-your-trip/routes/' + $(this).val();
        });
      });

      once('outbound-panel', '.outbound-panel').forEach(function (element) {
        $(element).on('click', function () {
          let currentTitle = $('.outbound-route-map-toggle').html();
          $('.outbound-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#outbound-map-body').toggleClass('hide').toggleClass('show');
          const otherChevron = document.querySelector('#map-title-inbound #map-chevron span');
          otherChevron.style.transform === 'rotate(180deg)' ? otherChevron.style.transform = 'rotate(0deg)' : otherChevron.style.transform = 'rotate(180deg)';
          if (!$(this).hasClass('already-opened')) {
            initMap_outbound();
            $(this).addClass('already-opened');
          }
          $('.inbound-panel').each(function () {
            let currentTitle = $('.inbound-route-map-toggle').html();
            $('.inbound-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
            $('#inbound-map-body').toggleClass('hide').toggleClass('show');
            if (!$(this).hasClass('already-opened')) {
              initMap_inbound();
              $(this).addClass('already-opened');
            }
          });
        });
      });

      once('inbound-panel', '.inbound-panel').forEach(function (element) {
        $(element).on('click', function () {
          let currentTitle = $('.inbound-route-map-toggle').html();
          $('.inbound-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
          $('#inbound-map-body').toggleClass('hide').toggleClass('show');
          const otherChevron = document.querySelector('#map-title-outbound #map-chevron span');
          otherChevron.style.transform === 'rotate(180deg)' ? otherChevron.style.transform = 'rotate(0deg)' : otherChevron.style.transform = 'rotate(180deg)';
          if (!$(this).hasClass('already-opened')) {
            initMap_inbound();
            $(this).addClass('already-opened');
          }
          $('.outbound-panel').each(function () {
            let currentTitle = $('.outbound-route-map-toggle').html();
            $('.outbound-route-map-toggle').html(currentTitle.includes('Open') ? currentTitle.replace('Open', 'Close') : currentTitle.replace('Close', 'Open'));
            $('#outbound-map-body').toggleClass('hide').toggleClass('show');
            if (!$(this).hasClass('already-opened')) {
              initMap_outbound();
              $(this).addClass('already-opened');
            }
          });
        });
      });

      once('bindclick', '.panel-title .glyphicon').forEach(function (element) {
        $(element).on('click', function (event) {
          $(event.target).closest('.outbound-panel').find('a[data-toggle]').click();
          $(event.target).closest('.inbound-panel').find('a[data-toggle]').click();
        });
      });

      once('inbound-panel', '.inbound-panel').forEach(function (element) {
        $(element).on('show.bs.collapse', function () {
          $('.inbound-route-map-toggle').html('Hide Map');
          if (!$(this).hasClass('already-opened')) {
            // initMap_inbound();
            $(this).addClass('already-opened');
          }
        });
      });

      once('stops-panel', '.stops-panel').forEach(function (element) {
        $(element).on('hide.bs.collapse', function () {
          $('.stops-map-toggle').html('Show Stops Map');
        });
      });

      once('stops-panel', '.stops-panel').forEach(function (element) {
        $(element).on('show.bs.collapse', function () {
          $('.stops-map-toggle').html('Hide Stops Map');
          if (!$(this).hasClass('already-opened')) {
            $(this).addClass('already-opened');
          }
        });
      });

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

})(jQuery, Drupal, once);
