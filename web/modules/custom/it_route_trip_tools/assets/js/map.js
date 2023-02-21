$(document).ready(function() {
  $.get("https://gtfs.intercitytransit.com/routedata.php?route_short_name=13&service_id=1.0&trip_headsign=Outbound", function(data, status) {
    var routeData = JSON.parse(data);

    const map = new google.maps.Map(document.getElementById("map"), {
      mapTypeId: routeData.map.mapTypeId,
    });

    fitMapBounds(map, routeData.map.boundingBox);

    $.each(routeData.shapes, function(shapesKey, shapesData) {
      plotBusRoute(map, shapesData.shapeData);
    });

    $.each(routeData.stops, function(stopKey, stopData) {
      if (stopData.timepoint == null) {
        plotStop(map, stopData.stopId, stopData.stopName, stopData.stopLat, stopData.stopLon);
      }
      else {
       plotStop(map, stopData.stopId, stopData.stopName, stopData.stopLat, stopData.stopLon)
     }
   });
  });
});

function plotStop(map, stop_id, stop_name, stop_lat, stop_long) {

  const latlong = {
    lat: stop_lat,
    lng: stop_long
  };
  new google.maps.Marker({
    position: latlong,
    map: map,
    title: (stop_id + ": " + stop_name),
    icon: {
      path: google.maps.SymbolPath.CIRCLE,
      scale: 3,
      strokeColor: "blue",
      fillColor: "white",
      fillOpacity: 0.8,
      strokeWeight: 2
    }
  });

}

function fitMapBounds(map, boundingBox) {
  var latlng = [
  new google.maps.LatLng(boundingBox.min.lat, boundingBox.min.lng),
  new google.maps.LatLng(boundingBox.max.lat, boundingBox.max.lng),
  ];
  var latlngbounds = new google.maps.LatLngBounds();
  for (var i = 0; i < latlng.length; i++) {
    latlngbounds.extend(latlng[i]);
  }
  map.fitBounds(latlngbounds);
}

function plotBusRoute(map, shapeData) {
  //console.log(shapeData);
  const shapePath = [];

  for (var index = 0; index < shapeData.length; index++) {
    shapePath[index] = {
      lat: shapeData[index].shape_pt_lat,
      lng: shapeData[index].shape_pt_lon
    };

  }
  //console.log(shapePath);
  const routePath = new google.maps.Polyline({
    path: shapePath,
    geodesic: true,
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 2,
  });
  routePath.setMap(map);
}
