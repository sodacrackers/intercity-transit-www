import * as React from 'react';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import Spinner from 'react-bootstrap/Spinner';
import { DateTime } from 'luxon';
import GoogleMapReact from 'google-map-react';
import styles from './index.module.css';

const RealTimeArrivals = () => {
  const [data, setData] = React.useState({});
  const [sanitizedData, setSanitizedData] = React.useState({});
  const [direction, setDirection] = React.useState('inbound');
  const [view, setView] = React.useState('wait');
  const [loading, setLoading] = React.useState(true);
  const [nonTimepointsHidden, setNonTimepointsHidden] = React.useState(false);

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
  }, [direction, view])

  return Object.keys(data).length && !loading ? (
    <Container className={styles.routesWrapper}>
      <Row>
        <div style={{ width: '100%', height: '477px', marginBottom: '32px'}}>
          <GoogleMapReact
           bootstrapURLKeys={{ key: "AIzaSyC-X7W8qAAeZP-dG3qZzlqrTJG6l8tddf8" }} // TODO: add to .env with prod creds
           defaultCenter={{
              lat: ((data?.bounding?.max?.lat + data?.bounding?.min?.lat) / 2),
              lng: ((data?.bounding?.max?.lng + data?.bounding?.min?.lng) / 2)
            }}
           defaultZoom={13}
          />
        </div>
        <Col className="order-5 order-lg-1" lg="8" xs="12">
          <Row>
            <Row className={styles.filterSection}>
              <Col xs="12" lg="4" style={{ maxWidth: '222px' }}>
                <div className={styles.timepointSwitch}>
                  <button onClick={() => setNonTimepointsHidden(false)} className={!nonTimepointsHidden ? styles.timepointSwitchActive : styles.timepointSwitchInactive}>All Stops</button>
                  <button onClick={() => setNonTimepointsHidden(true)} className={nonTimepointsHidden ? styles.timepointSwitchActive : styles.timepointSwitchInactive}>Timepoints</button>
                </div>
              </Col>
              <Col xs="12" lg="4" className="px-4">
                <div className="w-100"><strong>Direction:</strong></div>
                <Row className="d-block">
                  <Form.Check
                    type="radio"
                    label="Inbound"
                    value="inbound"
                    onClick={() => setDirection('inbound')}
                    checked={direction === 'inbound'}
                    className={styles.formCheck}
                  />
                  <Form.Check
                    type="radio"
                    label="Outbound"
                    value="outbound"
                    onClick={() => setDirection('outbound')}
                    checked={direction === 'outbound'}
                    className={styles.formCheck}
                  />
                </Row>
              </Col>
              <Col xs="12" lg="4" className="px-4">
                <div className="w-100"><strong>View Next Arrival As:</strong></div>
                <Row className="d-block">
                  <Form.Check
                    type="radio"
                    label="Minutes to Wait"
                    value="wait"
                    onClick={() => setView('wait')}
                    checked={view === 'wait'}
                    className={styles.formCheck}
                  />
                  <Form.Check
                    type="radio"
                    label="Est. Departure Time"
                    value="departure"
                    onClick={() => setView('departure')}
                    checked={view === 'departure'}
                    className={styles.formCheck}
                  />
                </Row>
              </Col>
            </Row>
            <Col xs="12">
              {Object.keys(sanitizedData)?.map((_, stopIndex) => {
                const stopId = Number(sanitizedData[stopIndex][0].stopId);
                const stopObj = data.stop_markers[direction][stopId];
                if (stopObj && Object.keys(stopObj).length > 0) {
                  const stopTimes = stopObj?.stop_times;
                  const isTimepoint = data.stop_markers[direction][stopId].stop_data.timepoint > 0;
                  const now = DateTime.now().toMillis();
                  const delay = Number(data.stop_markers[direction][stopId].real_time.arrival?.delay) / 60;
                  let firstItemIndex = stopTimes?.findIndex((item) => {
                    if (DateTime.fromFormat(item, 'h:mm a').toMillis() > DateTime.now().toMillis()) {
                      return true;
                    }
                    return false;
                  });
                  const arrivalTimeRaw = firstItemIndex > -1 ? `${DateTime.now().toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[firstItemIndex], 'h:mm a').toFormat('HH:mm')}` : `${DateTime.now().plus({ days: 1 }).toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[0], 'h:mm a').toFormat('HH:mm')}`;
                  const arrivalTimeRawNext = firstItemIndex > -1 && stopTimes[firstItemIndex + 1] ? `${DateTime.now().toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[firstItemIndex + 1], 'h:mm a').toFormat('HH:mm')}` : `${DateTime.now().plus({ days: 1 }).toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[1], 'h:mm a').toFormat('HH:mm')}`;
                  const arrivalTimeRawLast = firstItemIndex > -1 && stopTimes[firstItemIndex + 2] ? `${DateTime.now().toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[firstItemIndex + 2], 'h:mm a').toFormat('HH:mm')}` :  `${DateTime.now().plus({ days: 1 }).toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(stopTimes[2], 'h:mm a').toFormat('HH:mm')}`;
                  const waitTime = arrivalTimeRaw && ((DateTime.fromSQL(arrivalTimeRaw).toMillis() - now) / 60000).toFixed(0);
                  const waitTimeString = waitTime < 60 ? `${waitTime} min` : `${Math.floor(((DateTime.fromSQL(arrivalTimeRaw).toMillis() - now) / 3600000))} hr ${waitTime % 60} min`;
                  const waitTimeNext = arrivalTimeRawNext && ((DateTime.fromSQL(arrivalTimeRawNext).toMillis() - now) / 60000).toFixed(0);
                  const waitTimeStringNext = waitTimeNext < 60 ? `${waitTimeNext} min` : `${Math.floor(((DateTime.fromSQL(arrivalTimeRawNext).toMillis() - now) / 3600000))} hr ${waitTimeNext % 60} min`;
                  const waitTimeLast = arrivalTimeRawLast && ((DateTime.fromSQL(arrivalTimeRawLast).toMillis() - now) / 60000).toFixed(0);
                  const waitTimeStringLast = waitTimeLast < 60 ? `${waitTimeLast} min` : `${Math.floor(((DateTime.fromSQL(arrivalTimeRawLast).toMillis() - now) / 3600000))} hr ${waitTimeLast % 60} min`;
                  return (
                    <>
                      {!(!isTimepoint && nonTimepointsHidden) && (
                        <Row className={isTimepoint ? styles.timepoint : styles.stopInfo}>
                          <Col md="6" key={`stopName-${stopIndex}`}>
                            {isTimepoint ? <div className={styles.timepointMarker}>Timepoint</div> : <div class={styles.dot} />}
                            <div className={isTimepoint ? styles.timepointInfo : styles.nonTimepointInfo}>{stopObj?.stop_data.stopName}</div>
                          </Col>
                          <Col md="6" className={isTimepoint ? styles.timepointRight : styles.right}>
                            {waitTime && (
                              <div className={
                                delay > 0 
                                ? styles.lateArrivalTag
                                : delay < 0
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ? waitTimeString : stopTimes[firstItemIndex === -1 ? 0 : firstItemIndex]}
                              </div>
                            )}
                            {waitTimeNext && (
                              <div className={
                                delay > 0 
                                ? styles.lateArrivalTag
                                : delay < 0
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ?waitTimeStringNext : stopTimes[firstItemIndex === -1 ? 1 : firstItemIndex + 1]}
                              </div>
                            )}
                            {waitTimeLast && (
                              <div className={
                                delay > 0 
                                ? styles.lateArrivalTag
                                : delay < 0
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ? waitTimeStringLast : stopTimes[firstItemIndex === -1 ? 2 : firstItemIndex + 2]}
                              </div>
                            )}
                          </Col>
                        </Row>
                      )}
                      {isTimepoint && Object.keys(sanitizedData)[stopIndex + 1]
                        ? (
                          <>
                            <div class={styles.empty} />
                            <div className={nonTimepointsHidden ? styles.show : styles.hide} onClick={() => setNonTimepointsHidden(!nonTimepointsHidden)}>
                              <div class={styles.dot} />
                              <div class="d-inline-block">{nonTimepointsHidden ? 'Show' : 'Hide'} Non-Timepoint Stops</div>
                              </div>
                            {nonTimepointsHidden && <div class={styles.empty} />}
                          </>
                        ): null}
                    </>
                  )
                }
              })}
            </Col>
          </Row>
        </Col>
        <Col className="order-1 order-lg-5 mb-5 px-5" xs="12" lg="4">
          <div className={styles.legend}>
            <div className={styles.legendTitle}>Arrivals Info &amp; Legend</div>
            <Row className="mr-0">
              <Col xs="4" className="pr-3"> <div style={{ marginLeft: 0 }} className={styles.arrivalTag}>On Time</div></Col>
              <Col xs="4" className="pr-3 pl-3"> <div style={{ marginLeft: 0 }} className={styles.earlyArrivalTag}>Early</div></Col>
              <Col xs="4" className="pl-3"> <div style={{ marginLeft: 0 }}className={styles.lateArrivalTag}>Late</div></Col>
            </Row>
            <div className={styles.legendText}>
              <div className="mb-4">Estimated arrival times are based on real-time data.</div>
              <div>The times listed correspond to a bus that is currently on this route.</div>
            </div>
          </div>
        </Col>
      </Row>
    </Container>
  ) : <div className="mt-5 text-center"><h2>Loading Real Time Information...</h2><Spinner style={{ width: '10rem', height: '10rem' }}className={styles.spinner} variant="success" /></div>
}

export default RealTimeArrivals;
