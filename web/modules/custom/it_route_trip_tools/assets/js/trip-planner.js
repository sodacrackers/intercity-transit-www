(function ($) {
    $(document).ready(function(){
        $('form #tripplanner-menu .ui-timepicker-select').change( function(){
          var selected_time = $(this).val()
          $('#time_input').val(selected_time);
        });
        //Adds timepicker dropdown for Trip Planner and Find a Route time fields
        $('#time').timepicker();
        $('#time').timepicker('option', {'useSelect': true, 'timeFormat': 'g:i a', 'setTime': Date.now(), 'step': 5, 'className': 'form-control timepicker-content'});

        $('#route-time').timepicker();
        $('#route-time').timepicker('option', {'useSelect': true, 'timeFormat': 'g:i a', 'setTime': Date.now(), 'step': 5, 'className': 'form-control timepicker-content'});


        //Adds jQuery Datepicker function to Trip Planner and Find a Route date fields
        $('input#date').datepicker();
        $('input#date').datepicker('setDate', new Date());

        //Adds time 1 and 2 IDs to the time input fields. The timepicker tool strips these tags away so they have to be replaced.
        $('#timepicker-content').attr('id', 'time').attr('name', 'time');
        $('#timepicker-menu').attr('id', 'time1').attr('name', 'time');
        $('.address-clear').click(function(){
            var clickedClass = 'input.' + this.id;
            $(clickedClass).each(function(){
                $(this).val('');
            });
        });

        var $ = jQuery;
        var stop_selected = $('#stops-form option:selected').val();
        stop_selected = stop_selected && stop_selected != 'Select a stop';
        $('#stops-form button').prop('disabled', stop_selected ? false : true);
        $('#stops-form select').change(function() {
            var val = $(this).find(":selected").val();
            val = val && val != 'Select a stop';
            if (val) {
                $('#stops-form button').prop('disabled', false);
            }
            else {
                $('#stops-form button').prop('disabled', true);
            }
        });
    });
})(jQuery);
