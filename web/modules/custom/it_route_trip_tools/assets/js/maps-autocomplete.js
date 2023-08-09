(function ($) {
  $(document).ready(function(){
    var start_add_input = document.getElementById('start_add');
    var start_add_id_input = document.getElementById('start_add_id');
    var dest_add_input = document.getElementById('dest_add');
    var dest_add_id_input = document.getElementById('dest_add_id');
    var startErrorDiv = document.getElementById('start-add-alert');
    var destErrorDiv = document.getElementById('dest-add-alert');

    if (start_add_input) {
      start_add_input.addEventListener('change', function() {
        if (start_add_id_input.value == '') {
          startErrorDiv.classList.remove('hidden');
          startErrorDiv.innerHTML = 'Please enter a valid start address';
        }
      });
    }
    if (dest_add_input) {
      dest_add_input.addEventListener('change', function() {
        if (dest_add_id_input.value == '') {
          destErrorDiv.classList.remove('hidden');
          destErrorDiv.innerHTML = 'Please enter a valid destination address';
        }
      });      
    }
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
function initMapAutocomplete() {
  startaddress = new google.maps.places.Autocomplete(
    (document.getElementById('start_add')),{
      fields: ['place_id'],
    }
  );
  destaddress = new google.maps.places.Autocomplete(
    (document.getElementById('dest_add')),{
      fields: ['place_id'],
    }
  );
  startaddress.addListener('place_changed', getStartId);
  destaddress.addListener('place_changed', getDestId);
}
function getStartId() {
  // Get the place details from the autocomplete object.
  var start_place = startaddress.getPlace();
  document.getElementById('start_add_id').value = (start_place.place_id);
  if (document.getElementById('start_add_id').value != '' &&  document.getElementById('dest_add_id').value != '') {
    document.getElementById('get-directions').classList.remove('disabled');
  }
  if (start_place.place_id != '') {
    var start_add_input = document.getElementById('start_add');
    var start_add_id_input = document.getElementById('start_add_id');
    var startErrorDiv = document.getElementById('start-add-alert');
    startErrorDiv.classList.add('hidden');
    startErrorDiv.innerHTML = '';
  } 
}
function getDestId() {
  // Get the place details from the autocomplete object.
  var dest_place = destaddress.getPlace();
  document.getElementById('dest_add_id').value = (dest_place.place_id);
  if (document.getElementById('start_add_id').value != '' &&  document.getElementById('dest_add_id').value != '') {
    document.getElementById('get-directions').classList.remove('disabled');
  }
  if (dest_place.place_id != '') {
    var dest_add_input = document.getElementById('dest_add');
    var dest_add_id_input = document.getElementById('dest_add_id');
    var destErrorDiv = document.getElementById('dest-add-alert');
    destErrorDiv.classList.add('hidden');
    destErrorDiv.innerHTML = '';
  }
}