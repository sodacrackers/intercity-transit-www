import * as React from 'react';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import { DateTime } from 'luxon';

const RealTimeArrivals = () => {
  const [tripUpdates, setTripUpdates] = React.useState({});
  const [activeRoutes, setActiveRoutes] = React.useState([]);
  const [selectedRoute, setSelectedRoute] = React.useState(null);
  const [selectedRouteData, setSelectedRouteData] = React.useState(null);
  const [direction, setDirection] = React.useState('inbound');
  const [view, setView] = React.useState('wait');
  const [stopResults, setStopResults] = React.useState({});

  const getData = async() => {
    try {
      const data = await fetch('https://localhost:49815/api/json/real-time-data', {
        headers: {
          'Access-Control-Allow-Origin': '*',
        }
      });
      const json = await data.json();
      setTripUpdates(json.TripUpdate.entity);
    } catch (err) {
      console.log(err);
    }
    return null;
  }

  React.useEffect(() => {
    const results = {};
    selectedRouteData?.forEach((item) => {
      item.stopTimeUpdate.forEach((stop) => {
        const index = Object.keys(results).indexOf(`Stop ${stop.stopId}`);
        if (index > -1) {
          results[`Stop ${stop.stopId}`].push({ vehicle: item.vehicle?.id, arrival: stop.arrival?.time, departure: stop.departure?.time  });
          results[`Stop ${stop.stopId}`].sort((a, b) => DateTime.fromMillis(Number(a.arrival * 1000 < DateTime.fromMillis(Number(b.arrival * 1000)))));
        } else {
          results[`Stop ${stop.stopId}`] = [{ vehicle: item.vehicle?.id, arrival: stop.arrival?.time, departure: stop.departure?.time  }];
        }
        setStopResults((currentResults) => ({...currentResults, ...results}));
      })
    });
  }, [selectedRouteData])

  React.useEffect(() => {
    const routes = Object.keys(tripUpdates).map((key) => {
      return Number(tripUpdates[key].tripUpdate?.trip.routeId);
    })
    const activeRoutesCollection = [...new Set(routes)];
    setActiveRoutes(activeRoutesCollection);
  }, [tripUpdates])

  React.useEffect(() => {
    const activeBuses = Object.keys(tripUpdates).filter((key) => {
      if (Number(tripUpdates[key].tripUpdate?.trip?.routeId) === selectedRoute) {
        return key;
      }
    })
    const data = [];
    activeBuses.forEach((bus) => {
      data.push(tripUpdates[bus].tripUpdate);
    })
    setSelectedRouteData(data);
  }, [selectedRoute])

  React.useEffect(() => {
    getData();
  }, [])

  return (
    <Container>
      <Row>
        {selectedRoute && (<div className="text-center">There are <strong>{selectedRouteData.length} buses</strong> currently on this route. Data is updated in real time.</div>)}
        <Col sm="9">
          <Row>
            <Col>
              <Form.Select onChange={e => setSelectedRoute(Number(e.target.value))}>
                <option value={null}>Please select a route...</option>            
                {activeRoutes.map((route) => (
                  <option value={route}>Route {route}</option>
                ))}
              </Form.Select>
            </Col>
            <Col>
              <Form.Check
                type="radio"
                label="Inbound"
                value="inbound"
                onClick={() => setDirection('inbound')}
                checked={direction === 'inbound'}
              />
              <Form.Check
                type="radio"
                label="Outbound"
                value="outbound"
                onClick={() => setDirection('outbound')}
                checked={direction === 'outbound'}
              />
            </Col>
            <Col>
              <Form.Check
                type="radio"
                label="Minutes to Wait"
                value="wait"
                onClick={() => setView('wait')}
                checked={view === 'wait'}
              />
              <Form.Check
                type="radio"
                label="Est. Departure Time"
                value="departure"
                onClick={() => setView('departure')}
                checked={view === 'departure'}
              />
            </Col>
            <Col xs="12">
              {Object.keys(stopResults)?.map((_, index) => {
                const positiveDiff = stopResults[Object.keys(stopResults)[index]]?.filter((stat) => {
                  const now = DateTime.local();
                  const upcoming =  DateTime.fromMillis(Number(stat.arrival * 1000));
                  const diff = (now.diff(upcoming, 'minutes').toObject());
                  return diff.minutes?.toFixed(0) > 0;
                }).length > 0;
                return positiveDiff && (
                  <>
                    <div className="d-inline-block">{Object.keys(stopResults)[index]}</div>
                    {stopResults[Object.keys(stopResults)[index]]?.map((stopStat) => {
                      const newNow = DateTime.local();
                      const newUpcoming =  DateTime.fromMillis(Number(stopStat.arrival * 1000));
                      const difference = (newNow.diff(newUpcoming, 'minutes').toObject());
                      return (
                        <div>
                          {difference.minutes?.toFixed(0) > 0 && (
                            <>
                              <div className="d-inline-block mx-5">{`${difference.minutes?.toFixed(0)} Minutes`}</div>
                            </>
                          )}
                        </div>
                      )
                    })}
                  </>
                )
              })}
            </Col>
          </Row>
        </Col>
        <Col sm="3">
          Legend
        </Col>
      </Row>
    </Container>
  );
}

export default RealTimeArrivals;
