function showPosition() {
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var latlngVal = lat + ',' + lng;

            const geocoder = new google.maps.Geocoder();
            console.log(geocoder);
            geocodeLatLng(geocoder, latlngVal);
        });
    } else {
      var tripErrorDiv = document.getElementById('trip-errors');
      tripErrorDiv.innerHTML = 'Your browser does not support the geolocation feature.';
      tripErrorDiv.classList.remove('hidden');
  }
}


function geocodeLatLng(geocoder, latlngVal) {
    const latlngStr = latlngVal.split(",", 2);
    const latlng = {
        lat: parseFloat(latlngStr[0]),
        lng: parseFloat(latlngStr[1]),
    };
    geocoder.geocode({ location: latlng }, (results, status) => {
        if (status === "OK") {
            if (results[0]) {
                  var start_add_id = results[0].place_id;
                  var start_add = results[0].formatted_address;
                  document.getElementById('start_add_id').value = start_add_id;
                  document.getElementById('start_add').value = start_add;
              }
              else {
                  var tripErrorDiv = document.getElementById('trip-errors');
                  tripErrorDiv.innerHTML = 'Sorry, no results found for that route.';
                  tripErrorDiv.classList.remove('hidden');
              }
          }
          else {
              var tripErrorDiv = document.getElementById('trip-errors');
              tripErrorDiv.innerHTML = 'Your browser does not support the geolocation feature.';
              tripErrorDiv.classList.remove('hidden');
          }
      });
}