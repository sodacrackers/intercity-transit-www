import * as React from 'react';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import Image from 'react-bootstrap/Image'
import Spinner from 'react-bootstrap/Spinner';
import GoogleMapReact from 'google-map-react';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';
import Table from 'react-bootstrap/Table';
import { DateTime } from 'luxon';

import alarmClock from '../../assets/alarm-clock.svg';
import circleCheck from '../../assets/circle-check.svg';
import circleExclamation from '../../assets/circle-exclamation.svg';
import symbolGreen from '../../assets/symbol-green.svg';
import symbolPurple from '../../assets/symbol-purple.svg';
import symbolRed from '../../assets/symbol-red.svg';
import info from '../../assets/info.svg';

import styles from './index.module.css';


const RealTimeArrivals = () => {
  const [data, setData] = React.useState({});
  const [sanitizedData, setSanitizedData] = React.useState({});
  const [direction, setDirection] = React.useState('inbound');
  const [view, setView] = React.useState('wait');
  const [loading, setLoading] = React.useState(true);
  const [nonTimepointsHidden, setNonTimepointsHidden] = React.useState(false);
  const [coordinates, setCoordinates] = React.useState([]);

  const getData = async(apiUrl) => {
    try {
      const data = await fetch(`${window.location.origin}${apiUrl}`, {
        headers: {
          'Access-Control-Allow-Origin': '*',
        }
      });
      const json = await data.json();
      const clean = {};
      const coords = [];
      json.trips[direction].forEach((trip) => {
        trip.stopTimes.forEach((st) => {
          clean[st.stopSequence] = clean[st.stopSequence] ? [...clean[st.stopSequence], st] : [st];
        })
      })
      Object.keys(json.stop_markers[direction]).forEach((stopMarkerKey) => {
        coords.push({
          lat: json?.stop_markers[direction][stopMarkerKey]?.stop_data?.stopLat,
          lng: json?.stop_markers[direction][stopMarkerKey]?.stop_data?.stopLon,
          seq: json?.stop_markers[direction][stopMarkerKey]?.stop_data?.stopSequence,
        })
      })
      coords.sort((a, b) => a.seq - b.seq)

      setData(json);
      setCoordinates(coords);
      setSanitizedData(clean);
      setLoading(false);
    } catch (err) {
      console.log(err);
    }
  }

  const renderPolylines = async(map, maps) => {
    let geodesicPolyline = new maps.Polyline({
      path: coordinates,
      strokeColor: "#007B5F",
      strokeOpacity: 1.0,
      strokeWeight: 2,
    })
    geodesicPolyline.setMap(map)
  }

  const getNextStop = (stopData) => {
    const step = stopData.stopSequence;
    const newVal = Object.values(data?.stop_markers[direction]).find(item => {
      return item.stop_data.stopSequence === step + 1
    });
    return newVal?.stop_data?.stopName;
  }

  React.useEffect(() => {
    const apiUrl = document.getElementById('ict-routes-react-app').dataset.apiUrl;
    setLoading(true);
    getData(apiUrl);
  }, [direction, view])

  return Object.keys(data).length && !loading ? (
    <>
      <div className={styles.mapContainer} style={{ width: '100%', height: '477px', marginBottom: '32px' }}>
        <GoogleMapReact
          bootstrapURLKeys={{ key: "AIzaSyC-X7W8qAAeZP-dG3qZzlqrTJG6l8tddf8" }} // TODO: add to .env with prod creds
          defaultCenter={{
              lat: data?.center?.lat,
              lng: data?.center?.lng
            }}
          defaultZoom={12}
          onGoogleApiLoaded={({map, maps}) => renderPolylines(map, maps)}
        >
        {Object.keys(data?.stop_markers[direction]).map((stopKey) => {
          return (
            <OverlayTrigger
              placement="top"
              delay={{ show: 150, hide: 300 }}
              lat={data?.stop_markers[direction][stopKey].stop_data?.stopLat}
              lng={data?.stop_markers[direction][stopKey].stop_data?.stopLon}
              overlay={
                <Tooltip className={styles.toolTipMap}>
                  <button className={styles.closeButton}>x</button>
                  <div>
                    <h4>{data?.stop_markers[direction][stopKey].stop_data?.stopName} - Stop {data?.stop_markers[direction][stopKey].stop_data?.stopId}</h4>
                    {getNextStop(data?.stop_markers[direction][stopKey].stop_data) && <div><strong>Headed to {getNextStop(data?.stop_markers[direction][stopKey].stop_data)}</strong></div>}
                    {data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[0]]?.vehicle_label ?
                      <Table className="d-table" striped bordered hover responsive>
                        <thead>
                          <tr>
                            <th className="col-2">Bus</th>
                            <th className="col-3">ETA</th>
                            <th className="col-7">Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td className="col-2">{data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[0]]?.vehicle_label}</td>
                            <td className="col-3">{DateTime.fromMillis(Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[0]]?.arrival_time) * 1000).toLocal().toFormat('h:mm:ss')}</td>
                            <td className="col-7">{
                              Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[0]]?.arrival_delay) > 60 ? (
                                <div className={styles.datapointLate}>
                                  Late
                                </div>
                              ) : Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[0]]?.arrival_delay) < -60
                                ? (
                                  <div className={styles.datapointEarly}>
                                    Early
                                  </div>
                              ) : (
                                <div className={styles.datapointOnTime}>
                                  On Time
                                </div>
                              )
                            }</td>
                          </tr>
                          <tr>
                            <td className="col-2">{data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[1]]?.vehicle_label}</td>
                            <td className="col-3">{DateTime.fromMillis(Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[1]]?.arrival_time) * 1000).toLocal().toFormat('h:mm:ss')}</td>
                            <td className="col-7">{
                              Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[1]]?.arrival_delay) > 60 ? (
                                <div className={styles.datapointLate}>
                                  Late
                                </div>
                              ) : Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[1]]?.arrival_delay) < -60
                                ? (
                                  <div className={styles.datapointEarly}>
                                    Early
                                  </div>
                              ) : (
                                <div className={styles.datapointOnTime}>
                                  On Time
                                </div>
                              )
                            }</td>
                          </tr>
                          <tr>
                            <td className="col-2">{data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[2]]?.vehicle_label}</td>
                            <td className="col-3">{DateTime.fromMillis(Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[2]]?.arrival_time) * 1000).toLocal().toFormat('h:mm:ss')}</td>
                            <td className="col-7">{
                              Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[2]]?.arrival_delay) > 60 ? (
                                <div className={styles.datapointLate}>
                                  Late
                                </div>
                              ) : Number(data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey]?.real_time)[2]]?.arrival_delay) < -60
                                ? (
                                  <div className={styles.datapointEarly}>
                                    Early
                                  </div>
                              ) : (
                                <div className={styles.datapointOnTime}>
                                  On Time
                                </div>
                              )
                            }</td>
                          </tr>
                        </tbody>
                      </Table>
                    : <h4 className="text-center">End of the Line</h4>}
                  </div>
                </Tooltip>
              }
            >
              <div className={
                data?.stop_markers[direction][stopKey].stop_data.stopSequence === 0
                ? styles.startPoint
                : data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[0]]?.vehicle_label
                ? data?.stop_markers[direction][stopKey].stop_data.timepoint
                  ? styles.dataTimepoint
                  : styles.datapoint
                : styles.endPoint}
              >
                {data?.stop_markers[direction][stopKey].stop_data.stopSequence === 0 ? 'Start' : !data?.stop_markers[direction][stopKey]?.real_time[Object.keys(data?.stop_markers[direction][stopKey].real_time)[0]]?.vehicle_label ? 'End' : ''}
              </div>
            </OverlayTrigger>
          )
        })}
        </GoogleMapReact>
      </div>
      <Container className={styles.routesWrapper}>
        <Row>
          <Col className="order-5 order-xl-1 px-0" xl="8" xs="12">
            <Row>
              <Row className={styles.filterSection}>
                <Col className={styles.hideOnMobile} style={{ minWidth: '25%', paddingLeft: 0 }}>
                  <div className={styles.timepointSwitch}>
                    <button onClick={() => setNonTimepointsHidden(false)} className={!nonTimepointsHidden ? styles.timepointSwitchActive : styles.timepointSwitchInactive}>All Stops</button>
                    <button onClick={() => setNonTimepointsHidden(true)} className={nonTimepointsHidden ? styles.timepointSwitchActive : styles.timepointSwitchInactive}>Timepoints</button>
                  </div>
                  <OverlayTrigger
                    placement="top"
                    delay={{ show: 250, hide: 400 }}
                    overlay={
                      <Tooltip className={styles.toolTip}>
                        Buses do not leave <strong>Timepoints</strong> ahead of the published scheduled time.
                      </Tooltip>
                    }
                  >
                    <div className={styles.infoIcon}>
                      <Image src={info} />
                    </div>
                  </OverlayTrigger>
                </Col>
                <Col className={styles.growOnMobile}>
                  <div className="w-100"><strong className={styles.strong}>Direction:</strong></div>
                  <Row className={styles.formCheckRow}>
                    <Form.Check
                      type="radio"
                      label="Inbound"
                      value="inbound"
                      onClick={() => setDirection('inbound')}
                      checked={direction === 'inbound'}
                      className={styles.formCheckLeft}
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
                <Col className={styles.growOnMobile} style={{ minWidth: '300px' }}>
                  <div className="w-100"><strong className={styles.strong}>View Next Arrival As:</strong></div>
                  <Row className={styles.formCheckRow}>
                    <Form.Check
                      type="radio"
                      label="Minutes to Wait"
                      value="wait"
                      onClick={() => setView('wait')}
                      checked={view === 'wait'}
                      className={styles.formCheckLeft}
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
              <div className={styles.notification}>Buses do not leave <strong>Timepoints</strong> ahead of the published scheduled time.</div>
              <Col xs="12">
                {Object.keys(sanitizedData)?.map((_, stopIndex) => {
                  const stopId = Number(sanitizedData[stopIndex][0].stopId);
                  const stopObj = data.stop_markers[direction][stopId];
                  if (stopObj && Object.keys(stopObj).length > 0) {
                    const stopTimes = stopObj?.stop_times;
                    const formatDepartureTime = (index, changeDay) => changeDay 
                    ? DateTime.fromMillis(DateTime.fromFormat(stopTimes[index], 'h:mm:ss').plus({days: 1}).toMillis() + (delay * 1000))
                    : DateTime.fromMillis(DateTime.fromFormat(stopTimes[index], 'h:mm:ss').toMillis() + (delay * 1000));
                    const isTimepoint = data.stop_markers[direction][stopId].stop_data.timepoint > 0;
                    const now = DateTime.now().toMillis();
                    const firstItemIndex = stopTimes?.findIndex((item) => DateTime.fromSQL(`${DateTime.now().toFormat('yyyy-MM-dd')} ${DateTime.fromFormat(item, 'h:mm:ss').toFormat('HH:mm')}`).toMillis() > DateTime.now().toMillis());
                    const delay = Number(data.stop_markers[direction][stopId]?.real_time[Object.keys(data.stop_markers[direction][stopId]?.real_time)[0]]?.departure_delay) | 0;
                    console.log(delay);
                    const delayNext = Number(data.stop_markers[direction][stopId]?.real_time[Object.keys(data.stop_markers[direction][stopId]?.real_time)[1]]?.departure_delay) | 0;
                    const delayLast = Number(data.stop_markers[direction][stopId]?.real_time[Object.keys(data.stop_markers[direction][stopId]?.real_time)[2]]?.departure_delay) | 0;
                    const departureTimeFormatted = firstItemIndex > -1 
                      ?  formatDepartureTime(firstItemIndex)
                      :  formatDepartureTime(0, true);
                    const departureTimeFormattedNext = firstItemIndex > -1 
                      ? stopTimes.length >= firstItemIndex + 2
                        ? formatDepartureTime(firstItemIndex + 1)
                        : formatDepartureTime(0, true)
                      : formatDepartureTime(1, true);
                    const departureTimeFormattedLast = firstItemIndex > -1 
                      ? stopTimes.length >= firstItemIndex + 3
                        ? formatDepartureTime(firstItemIndex + 2)
                        : stopTimes.length === firstItemIndex + 1
                          ? formatDepartureTime(0, true)
                          : formatDepartureTime(1, true)
                      : formatDepartureTime(2, true);
                    console.log(departureTimeFormatted);
                    const waitTime = (Number(departureTimeFormatted.toMillis()) - now) / 60000;
                    const waitTimeNext = (Number(departureTimeFormattedNext.toMillis()) - now) / 60000;
                    const waitTimeLast = (Number(departureTimeFormattedLast.toMillis()) - now) / 60000;
                    const waitTimeString = (waitTime < 60 && waitTime > -60) ? `${Math.floor(waitTime)} min` : `${Math.floor(waitTime / 60)} hr ${Math.floor(waitTime % 60)} min`;
                    const waitTimeStringNext = (waitTimeNext < 60 && waitTimeNext > -60) ? `${Math.floor(waitTimeNext)} min` : `${Math.floor(waitTimeNext / 60)} hr ${Math.floor(waitTimeNext % 60)} min`;
                    const waitTimeStringLast = (waitTimeLast < 60 && waitTimeLast > -60) ? `${Math.floor(waitTimeLast)} min` : `${Math.floor(waitTimeLast / 60)} hr ${Math.floor(waitTimeLast % 60)} min`;
                    return (
                      <div className={(!isTimepoint && nonTimepointsHidden) ? styles.unmountedStyle : styles.mountedStyle} >
                        <Row className={isTimepoint ? styles.timepoint : styles.stopInfo}>
                          <Col md="6" className={!isTimepoint ? styles.stopCol : ''} key={`stopName-${stopIndex}`}>
                            {isTimepoint ? <div className={styles.timepointMarker}>Timepoint</div> : <div class={styles.dot} />}
                            <div className={isTimepoint ? styles.timepointInfo : styles.nonTimepointInfo}><span className={!isTimepoint ? styles.stopText : ''}>{stopObj?.stop_data.stopName}</span> {isTimepoint && <span className={styles.estimated}>Estimated</span>}</div>
                          </Col>
                          <Col md="6" className={isTimepoint ? styles.timepointRight : styles.right}>
                            {waitTime && (
                              <div className={
                                delay >= 60 
                                ? styles.lateArrivalTag
                                : delay <= -60
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ? <><Image className={styles.indicator} src={delay >= 60 ? circleExclamation : delay <= -60 ? alarmClock : circleCheck} /><span>{waitTimeString}</span><Image className={styles.shape} src={delay >= 60 ? symbolRed : delay <= -60 ? symbolPurple : symbolGreen} /></> : <><Image className={styles.indicator} src={delay >= 60 ? circleExclamation : delay <= -60 ? alarmClock : circleCheck} /><span>{departureTimeFormatted.toFormat('h:mm a')}</span><Image className={styles.shape} src={delay >= 60 ? symbolRed : delay <= -60 ? symbolPurple : symbolGreen} /></>}
                              </div>
                            )}
                            {waitTimeNext && (
                              <div className={
                                delayNext >= 60
                                ? styles.lateArrivalTag
                                : delayNext <= -60
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ? <><Image className={styles.indicator} src={delayNext >= 60 ? circleExclamation : delayNext <= -60 ? alarmClock : circleCheck} /><span>{waitTimeStringNext}</span><Image className={styles.shape} src={delayNext >= 60 ? symbolRed : delayNext <= -60 ? symbolPurple : symbolGreen} /></> : <><Image className={styles.indicator} src={delayNext >= 60 ? circleExclamation : delayNext <= -60 ? alarmClock : circleCheck} /><span>{departureTimeFormattedNext.toFormat('h:mm a')}</span><Image className={styles.shape} src={delayNext >= 60 ? symbolRed : delayNext <= -60 ? symbolPurple : symbolGreen} /></>}
                              </div>
                            )}
                            {waitTimeLast && (
                              <div className={
                                delayLast >= 60
                                ? styles.lateArrivalTag
                                : delayLast <= -60
                                  ? styles.earlyArrivalTag
                                  : styles.arrivalTag}>{view === 'wait' ? <><Image className={styles.indicator} src={delayLast >= 60 ? circleExclamation : delayLast <= -60 ? alarmClock : circleCheck} /><span>{waitTimeStringLast}</span><Image className={styles.shape} src={delayLast >= 60 ? symbolRed : delayLast <= -60 ? symbolPurple : symbolGreen} /></> : <><Image className={styles.indicator} src={delayLast >= 60 ? circleExclamation : delayLast <= -60 ? alarmClock : circleCheck} /><span>{departureTimeFormattedLast.toFormat('h:mm a')}</span><Image className={styles.shape} src={delayLast >= 60 ? symbolRed : delayLast <= -60 ? symbolPurple : symbolGreen} /></>}
                              </div>
                            )}
                          </Col>
                        </Row>
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
                      </div>
                    )
                  }
                })}
              </Col>
            </Row>
          </Col>
          <Col className="order-1 order-xl-5 mb-4" xs="12" xl="4">
            <div className={styles.legend}>
              <div className={styles.legendTitle}>Arrivals Info &amp; Legend</div>
              <div className="d-flex mb-3">
                <div style={{ marginLeft: 0, display: 'inline' }} className={styles.arrivalTag}><Image className={styles.indicator} src={circleCheck} />On Time<Image className={styles.shape} src={symbolGreen} /></div>
                <div style={{ marginLeft: '10px', display: 'inline' }} className={styles.earlyArrivalTag}><Image className={styles.indicator} src={alarmClock} />Early<Image className={styles.shape} src={symbolPurple} /></div>
                <div style={{ marginLeft: '10px', display: 'inline' }} className={styles.lateArrivalTag}><Image className={styles.indicator} src={circleExclamation} />Late<Image className={styles.shape} src={symbolRed} /></div>
              </div>
              <div className={styles.legendText}>
                <div className="mb-4">Estimated arrival times are based on real-time data.</div>
                <div>The times listed correspond to a bus that is currently on this route.</div>
              </div>
            </div>
          </Col>
        </Row>
      </Container>
    </>
  ) : <div className="mt-5 text-center"><h2>Loading Real Time Information...</h2><Spinner style={{ width: '10rem', height: '10rem' }} className={styles.spinner} variant="success" /></div>
}

export default RealTimeArrivals;
