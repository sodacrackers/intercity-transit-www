
(function ($) {
    $(document).ready(function(){
        $('#time').timepicker('setTime', new Date());
        $('form #tripplanner-menu .ui-timepicker-select').change( function(){
          var selected_time = $(this).val()
          $('#time-input').val(selected_time);
        });
    });
})(jQuery);



var placeSearch, startaddress, destaddress;



var stateBounds={
    wa: ["47.1853106", "-125.36955", "47.7510741", "-120.7401385"]
};

function getStateBounds(state) {
    return new google.maps.LatLngBounds(
      new google.maps.LatLng(stateBounds[state][0], 
                             stateBounds[state][1]), 
      new google.maps.LatLng(stateBounds[state][2], 
                             stateBounds[state][3])
    ); 
}


function initAutocomplete() {

  startaddress = new google.maps.places.Autocomplete(
    (document.getElementById('start_add')),
    {
      fields: ['place_id'],
      types: ['address'],
      componentRestrictions: {country: 'us'},
      bounds: getStateBounds('wa'),
    }
  );
  destaddress = new google.maps.places.Autocomplete(
    (document.getElementById('dest_add')),{
      fields: ['place_id'],
      types: ['address'],
      componentRestrictions: {country: 'us'},
      bounds: getStateBounds('wa'),
    }
  );
  startaddress.addListener('place_changed', getStartId);
  destaddress.addListener('place_changed', getDestId);
}

function getStartId() {
  // Get the place details from the autocomplete object.
  var start_place = startaddress.getPlace();
  document.getElementById('start_add_input').value = (start_place.place_id);
}
function getDestId() {
  // Get the place details from the autocomplete object.
  var dest_place = destaddress.getPlace();
  document.getElementById('dest_add_input').value = (dest_place.place_id);
}


function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: 47.0379,
        lng: -122.9007
      };
      var circle = new google.maps.Circle({
        center: geolocation,
        radius: position.coords.accuracy
      });
      autocomplete.setBounds(circle.getBounds());
    });
  }
}