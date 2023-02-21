(function ($, Drupal, drupalSettings) {
  'use strict';
  var done_once = 0;
  function build_the_stops_map(context) {
    if (done_once == 0) {
      done_once++;
      const shapesData = drupalSettings.it_route_trip_tools.stops_map_data_array;
      const stops=[];
      const map = new google.maps.Map(document.getElementById('stops-map'), {
        zoom: 11.5,
        center: {
          lat: 47.044408,
          lng: -122.901711
        },
        mapTypeId: "roadmap",
      });
      var stop_id = (drupalSettings.it_route_trip_tools.stop_id);
      var stop_name = (drupalSettings.it_route_trip_tools.stop_name);
      var stop_loc = {
        lat: drupalSettings.it_route_trip_tools.stop_lat,
        long: drupalSettings.it_route_trip_tools.stop_lon
      };
      plotStop(map, stop_id, stop_name, stop_loc.lat, stop_loc.long, stop_loc);
      var infoWindow = new google.maps.InfoWindow();
      $.each(shapesData[0], function(route, shape) {
        var shapeData = '';
        shapeData = shape.shapeData;
        var color = '#FFFFFF';
        plotBusRoute(map, shapeData, color, route, infoWindow);
      });
    }
  }
  Drupal.behaviors.it_route_trip_tools_stops_map = {
    attach(context, settings) {
      build_the_stops_map(context);
    }
  };

})(jQuery, Drupal, drupalSettings, document, window);

function plotStop(map, stop_id, stop_name, stop_lat, stop_long, stop_loc) {
  const latlong = {
    lat: stop_lat,
    lng: stop_long
  };
  new google.maps.Marker({
    position: latlong,
    map: map,
    title: (stop_id + ": " + stop_name)    
  });
}

function plotStopsCluster(map, stops) {
  new google.maps.Marker({
    position: stops,
    map: map
  });
}
function plotBusRoute(map, shapeData, color, routeName, infoWindow) {
  const shapePath = [];
  var routeName = routeName;
  var routesPath = drupalSettings.it_route_trip_tools.routes_path;  
  if (color == "#FFFFFF") {
    var color = '#' + Math.floor(Math.random()*16777215).toString(16);
  }
  for (var index = 0; index < shapeData.length; index++) {
    shapePath[index] = {
      lat: shapeData[index].shape_pt_lat,
      lng: shapeData[index].shape_pt_lon
    };
  }
  const routePath = new google.maps.Polyline({
    path: shapePath,
    geodesic: true,
    strokeColor: color,
    fillColor: '#747658',
    strokeOpacity: .5,
    strokeWeight: 3,
  });
  (function (routePath, routeName) {
    google.maps.event.addListener(routePath, 'click', function(e) {
      infoWindow.setPosition(e.latLng);
      infoWindow.setContent('<div style="width:200px;min-height:60px"><h3>Route ' + routeName + '</h3><a href="' + routesPath + '/' + routeName + '">View Route ' + routeName + ' details</a></div>');
      infoWindow.open(map, routePath);
    });
    google.maps.event.addListener(routePath, 'mouseover', function(event) {
      this.setOptions({
        strokeWeight: 5,
        strokeOpacity: 1
      });
    });
    google.maps.event.addListener(routePath, 'mouseout', function(event) {
      this.setOptions({
        strokeWeight: 3,
        strokeOpacity: .5
      });
    });
  })(routePath, routeName);
  routePath.setMap(map);
  initMapAutocomplete();
}