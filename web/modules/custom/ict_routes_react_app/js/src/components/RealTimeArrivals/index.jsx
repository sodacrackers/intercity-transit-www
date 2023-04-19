import * as React from 'react';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import Spinner from 'react-bootstrap/Spinner';
import { DateTime } from 'luxon';
import styles from './index.module.css';

const RealTimeArrivals = () => {
  const [tripUpdates, setTripUpdates] = React.useState({});
  const [activeRoutes, setActiveRoutes] = React.useState([]);
  const [selectedRoute, setSelectedRoute] = React.useState(null);
  const [data, setData] = React.useState({});
  const [sanitizedData, setSanitizedData] = React.useState({});
  const [direction, setDirection] = React.useState('inbound');
  const [view, setView] = React.useState('wait');
  const [loading, setLoading] = React.useState({});

  const getData = async(apiUrl) => {
    try {
      const data = await fetch(`${window.location.origin}${apiUrl}`, {
        headers: {
          'Access-Control-Allow-Origin': '*',
        }
      });
      const json = await data.json();
      const clean = {};
      json.trips[direction].forEach((trip) => {
        trip.stopTimes.forEach((st) => {
          clean[st.stopSequence] = clean[st.stopSequence] ? [...clean[st.stopSequence], st] : [st];
        })
      })
      setData(json);
      console.log(clean);
      setSanitizedData(clean);
      setLoading(false);
    } catch (err) {
      console.log(err);
    }
  }

  React.useEffect(() => {
    const apiUrl = document.getElementById('ict-routes-react-app').dataset.apiUrl;
    setLoading(true);
    getData(apiUrl);
  }, [direction])

  return Object.keys(data).length && !loading ? (
    <Container className={styles.routesWrapper}>
      <Row>
        <Col sm="9">
          <Row>
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
              {Object.keys(sanitizedData)?.map((stop, stopIndex) => (
                <Row>
                  <Col md="3" key={`stopName-${stopIndex}`}>
                    {sanitizedData[stop][0].stopName}
                  </Col>
                  <Col md="9">
                    {sanitizedData[stop]?.map((item) => item.departureTime)}
                  </Col>
                </Row>
              ))}
            </Col>
          </Row>
        </Col>
        <Col sm="3">
          Legend
        </Col>
      </Row>
    </Container>
  ) : <div className="mt-5 text-center"><Spinner variant="dark" /></div>
}

export default RealTimeArrivals;
