var stateBounds = {
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

function initMap() {
  var map = new google.maps.Map(document.getElementById('route-map'), {
    mapTypeControl: false,
    zoom: 13,
    center: {
      lat: 47.0379,
      lng: -122.9007
    }
  });
  new AutocompleteDirectionsHandler(map);
}



/**
 * @constructor
 */
 function AutocompleteDirectionsHandler(map) {
  this.map = map;
  this.originPlaceId = null;
  this.destinationPlaceId = null;
  var originInput = document.getElementById('start_add');
  var destinationInput = document.getElementById('dest_add');
  this.directionsService = new google.maps.DirectionsService;
  this.directionsDisplay = new google.maps.DirectionsRenderer;
  this.directionsDisplay.setMap(map);
  this.directionsDisplay.setPanel(document.getElementById('route-results'));

  var originAutocomplete = new google.maps.places.Autocomplete(
    originInput, {
      fields: ['place_id']
    });
  var destinationAutocomplete = new google.maps.places.Autocomplete(
    destinationInput, {
      fields: ['place_id']
    });

  this.setupPlaceChangedListener(originAutocomplete, 'ORIG');
  this.setupPlaceChangedListener(destinationAutocomplete, 'DEST');
}


AutocompleteDirectionsHandler.prototype.setupPlaceChangedListener = function(autocomplete, mode) {
  var me = this;
  autocomplete.bindTo('bounds', this.map);
  autocomplete.addListener('place_changed', function() {
    var place = autocomplete.getPlace();
    if (!place.place_id) {
      window.alert("Please select an option from the dropdown list.");
      return;
    }
    if (mode === 'ORIG') {
      me.originPlaceId = place.place_id;
    } else {
      me.destinationPlaceId = place.place_id;
    }
    document.getElementById('btn-map').addEventListener('click', function() {
      me.route();
    });
  });
};

AutocompleteDirectionsHandler.prototype.route = function() {
  if (!this.originPlaceId || !this.destinationPlaceId) {
    return;
  }
  
  var ttype = document.getElementById("ttype").value;  
  var routeopt = document.getElementById("opt").value;
  var date = document.getElementById('date').value;
  var time = document.getElementById("time-input").value;
  var datetime = new Date(Date.parse(date + " " + time));
  
  var me = this;
  
  if (ttype == "dep") {
    this.directionsService.route({
      origin: {
        'placeId': this.originPlaceId
      },
      destination: {
        'placeId': this.destinationPlaceId
      },
      travelMode: 'TRANSIT',
      provideRouteAlternatives: true,
      transitOptions: {
        departureTime: datetime,
        modes: ['BUS'],
        routingPreference: routeopt
      }
    }, function(response, status) {
      if (status === 'OK') {
        me.directionsDisplay.setDirections(response);
      } else {
        window.alert('Directions request failed due to ' + status);
      }
    });
  } else {
    this.directionsService.route({
      origin: {
        'placeId': this.originPlaceId
      },
      destination: {
        'placeId': this.destinationPlaceId
      },
      travelMode: 'TRANSIT',
      provideRouteAlternatives: true,
      transitOptions: {
        arrivalTime: datetime,
        modes: ['BUS'],
        routingPreference: routeopt
      }
    }, function(response, status) {
      if (status === 'OK') {
        me.directionsDisplay.setDirections(response);
      } else {
        window.alert('Directions request failed due to ' + status);
      }
    });
  }
};

(function ($, Drupal) {
  Drupal.behaviors.trip_planner_submitted = {
    attach: function (context, settings) {
      if (settings.trip_planner.submitted == 'yes' ) {
        var start_add = settings.trip_planner.start_add;
        var start_id = settings.trip_planner.start_id;
        var dest_add = settings.trip_planner.dest_add;
        var dest_id = settings.trip_planner.dest_id;
        var routeopt = settings.trip_planner.opt;
        var time = settings.trip_planner.time;
        var date = settings.trip_planner.date;
        var ttype = settings.trip_planner.ttype;
        var datetime = new Date(Date.parse(date + " " + time));

        alert ('start: ' + start_id + '<br>dest: ' + dest_id);
        var me = AutocompleteDirectionsHandler.prototype.route;

        if (ttype == "dep") {
          AutocompleteDirectionsHandler.prototype.route.directionsService.route({
            origin: {
              'placeId': AutocompleteDirectionsHandler.prototype.route.originPlaceId
            },
            destination: {
              'placeId': AutocompleteDirectionsHandler.prototype.route.destinationPlaceId
            },
            travelMode: 'TRANSIT',
            provideRouteAlternatives: true,
            transitOptions: {
              departureTime: datetime,
              modes: ['BUS'],
              routingPreference: routeopt
            }
          }, function(response, status) {
            if (status === 'OK') {
              me.directionsDisplay.setDirections(response);
            } else {
              window.alert('Directions request failed due to ' + status);
            }
          });
        }
      }
    }
  };
})(jQuery, Drupal);


