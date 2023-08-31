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

        var ob = new MutationObserver(function(m) {
            var inner_ob = new MutationObserver(function(subm) {
                const content = $(subm[0].target).find('.chosen-single span').first().html();
                  $('#stops-form button[type="submit"]').prop('disabled', content === 'Select a stop');
            });
            inner_ob.observe(m[0].addedNodes[0], { attributes: true, characterData: true, childList: true });
        });
        ob.observe(document.getElementById('stops-form'), { childList: true });
    });
})(jQuery);
