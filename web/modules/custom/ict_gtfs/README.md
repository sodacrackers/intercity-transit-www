# GTFS Data Import

This module fetches real-time data from a GTFS server, caches it, and returns
the value as a PHP array or a
[FeedMessage object](https://gtfs.org/realtime/reference/#message-feedmessage).

## Configuration

Configure the module at `/admin/config/services/ict-gtfs`:

- Base URL: the server providing real-time GTFS data, such as
  `https://its.rideralerts.com/InfoPoint/GTFS-realtime.ashx`.
- Maximum cache age: each call to the server will be cached for this many
  seconds.
- Allowed GTFS types: the types of data that your server supports, such as
  `Alert`, 'TripUpdate`, and `VehiclePosition`.
- Get JSON from GTFS object: if selected, then the module will always fetch data
  from the server in binary (Protocol Buffer) format. With currently available
  PHP libraries, this may lead to some deprecation notices. If not selected, and
  you request data as a PHP array, the module will fetch JSON from the server
  using the `debug=true` query parameter.

## Usage

The module defines the `ict.gtfs` service, which returns an object of the class
`Drupal\ict_gtfs\Gtfs`, which has the following public methods:

- `getAllowedTypes()`
- `getMaxAge()`
- `getJsonFromFeedMessage()`
- `getObject(string $type)`
- `getArray(string $type)`

For example:

```php
$gtfs = \Drupal::service('ict.gtfs');
$type = 'Alert';
assert(in_array($type, $gtfs->getAllowedTypes()));
$data_array = $gtfs->getArray($type);
assert(array_keys($data_array) === ['header', 'entity']);
$feed_message = $gtfs->getObject($type);
assert($feed_message instanceof \Google\Transit\Realtime\FeedMessage);
$header = $feed_message->getHeader();
assert($header instanceof \Google\Transit\Realtime\FeedHeader);
$timestamp = $header->getTimestamp();
```

The outer keys of the array returned by `getArray()` are always `header` and
`entity` (lower case). The inner keys depend on the `JSON from GTFS object`
setting.
