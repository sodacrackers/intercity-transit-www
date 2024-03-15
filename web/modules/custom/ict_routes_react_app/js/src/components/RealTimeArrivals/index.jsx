import * as React from 'react';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import Image from 'react-bootstrap/Image'
import Spinner from 'react-bootstrap/Spinner';
import Accordion from 'react-bootstrap/Accordion';
import Card from 'react-bootstrap/Card';
import { useAccordionButton } from 'react-bootstrap/AccordionButton';
import GoogleMapReact, { fitBounds } from 'google-map-react';
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
import chevronDown from '../../assets/chevron-down.svg';
import busWestward from '../../assets/bus-westward.svg';
import busEastward from '../../assets/bus-eastward.svg';

import styles from './index.module.css';


const RealTimeDepartures = () => {
  const [data, setData] = React.useState({});
  const [sanitizedData, setSanitizedData] = React.useState({});
  const [direction, setDirection] = React.useState('outbound');
  const [view, setView] = React.useState('wait');
  const [loading, setLoading] = React.useState(true);
  const [nonTimepointsHidden, setNonTimepointsHidden] = React.useState(false);
  const [coordinates, setCoordinates] = React.useState([]);
  const [centerAndZoom, setCenterAndZoom] = React.useState([]);
  const [mapVisible, setMapVisible] = React.useState(false);
  const [mapVisibleMemo, setMapVisibleMemo] = React.useState(false);
  const [keys, setKeys] = React.useState([]);
  const [orderedStops, setOrderedStops] = React.useState([]);

  const CustomToggle = ({ children, eventKey }) => {
    const decoratedOnClick = useAccordionButton(eventKey, () => {
      setMapVisible(!mapVisible);
    });
    return (
      <button
        type="button"
        id="mapAccordionButton"
        onClick={e => {
          e.preventDefault();
          decoratedOnClick();
        }}
        style={{ width: '100%', height: '100%', padding: '0', textAlign: 'left' }}
      >
        <div className={styles.mapAccordionToggle}>
          {children}
        </div>
        <span className={styles.mapAccordionLabel}>{mapVisible ? 'Close Real-Time Map' : 'Open Real-Time Map'}</span>
      </button>
    );
  }

  const getData = async (apiUrl) => {
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
          clean[st.stopId] = clean[st.stopId] ? [...clean[st.stopId], st.stopSequence] : [st.stopSequence];
        })
      })
      const tripOrder = Object.values(json.times_alt[direction].stops).map((item) => item.stop_id);
      let reordered = Object.entries(json.stop_markers[direction]).sort((a, b) => tripOrder.indexOf(a[1].stop_data.stopId) - tripOrder.indexOf(b[1].stop_data.stopId));
      const spliceIndices = [];
      reordered.forEach((item, index) => {
        if (reordered[index + 1]) {
          if (reordered[index + 1][1].stop_data.stopId === item[1].stop_data.stopId && (!!item[1].real_time && Object.keys(item[1].real_time)?.length) && (!!reordered[index + 1].real_time && !Object.keys(reordered[index + 1].real_time)?.length)) {
            item[1].stop_times = [...item[1].stop_times, ...reordered[index + 1][1].stop_times];
            item[1].real_time = { ...item[1].real_time, ...reordered[index + 1][1].real_time };
            spliceIndices.push(index + 1);
          } else if (reordered[index + 1][1].stop_data.stopId === item[1].stop_data.stopId && (!!item[1].real_time && !Object.keys(item[1].real_time)?.length) && (!!reordered[index + 1].real_time && Object.keys(reordered[index + 1].real_time)?.length)) {
            reordered[index + 1][1].stop_times = [...item[1].stop_times, ...reordered[index + 1][1].stop_times];
            reordered[index + 1][1].real_time = { ...item[1].real_time, ...reordered[index + 1][1].real_time };
            spliceIndices.push(index);
          } else if (reordered[index + 1][1].stop_data.stopId === item[1].stop_data.stopId && (item[1].stop_times.length < reordered[index + 1][1].stop_times.length || item[1].stop_times.length > reordered[index + 1][1].stop_times.length)) {
            item[1].stop_times = [...item[1].stop_times, ...reordered[index + 1][1].stop_times];
            item[1].real_time = { ...item[1].real_time, ...reordered[index + 1][1].real_time };
            spliceIndices.push(index + 1);
          } else if (reordered[index + 1][1].stop_data.stopId === item[1].stop_data.stopId && Object.keys(item[1].real_time).length === 0) {
            reordered[index + 1][1].stop_times = [...item[1].stop_times, ...reordered[index + 1][1].stop_times];
            reordered[index + 1][1].real_time = { ...item[1].real_time, ...reordered[index + 1][1].real_time };
            spliceIndices.push(index);
          }
        }
      })
      spliceIndices.sort((a, b) => b - a)
      spliceIndices.forEach((val) => {
        reordered.splice(val, 1);
      })
      reordered.sort((a, b) => Object.values(a[1].real_time)[0]?.stop_sequence - Object.values(b[1].real_time)[0]?.stop_sequence);
      const realTimeIndex = reordered.findIndex(item => (!!item[1].real_time && Object.keys(item[1].real_time)?.length > 0));
      if (realTimeIndex > -1) {
        reordered = reordered.filter((item, index) => {
          return (index + 1) === reordered.length ? true : !!item[1].real_time && Object.keys(item[1].real_time)?.length > 0;
        })
      }
      setOrderedStops(reordered);
      const mappedData = Object.entries(clean).sort((a, b) => Number(a[1][0]) - Number(b[1][0]));
      const sortedData = new Map();
      let sortingIndex = 0;
      for (let [key, value] of mappedData) {
        sortedData.set(sortingIndex, [key, value]);
        sortingIndex++;
      }
      const finalData = Object.fromEntries(sortedData);
      Object.keys(json[`${direction}_shapes`][json[`${direction}_shapes`].length - 1]).forEach((stopMarkerKey) => {
        coords.push({
          lat: Number(json[`${direction}_shapes`][json[`${direction}_shapes`].length - 1][stopMarkerKey]?.lat),
          lng: Number(json[`${direction}_shapes`][json[`${direction}_shapes`].length - 1][stopMarkerKey]?.lng),
        })
      })
      const { center, zoom } = fitBounds({
        ne: {
          lat: json.bounding?.max?.lat,
          lng: json.bounding?.max?.lng,
        },
        sw: {
          lat: json.bounding?.min?.lat,
          lng: json.bounding?.min?.lng,
        }
      }, {
        width: 600,
        height: 480,
      });
      const saneData = Object.keys(json.stop_markers[direction]).filter((marker) => {
        return Object.values(finalData).find((entry) => {
          return `${entry[0]}-${entry[1][0]}` === marker
        });
      });
      let cleanedData = [];
      Object.values(finalData).forEach((item, index) => {
        if (Object.keys(saneData).includes(index.toString())) {
          cleanedData.push([item[0], item[1]]);
        }
      });
      cleanedData = Object.fromEntries(cleanedData);
      setCenterAndZoom([center, zoom])
      setData(json);
      setCoordinates(coords);
      setSanitizedData(cleanedData);
      let keysList = [];
      Object.keys(json?.stop_markers[direction]).forEach((key) => {
        Object.keys(cleanedData).forEach((item) => {
          if (item === key.slice(0, 3)) {
            keysList.push(item);
          }
        })
      })
      setKeys(keysList);
    } catch (err) {
      console.error(err);
    }
  }

  React.useEffect(() => {
    if (keys.length && loading) {
      setLoading(false);
    }
    if (mapVisibleMemo) {
      const interval = setInterval(() => {
        const el = document.getElementById('mapAccordionButton');
        if (el) {
          el.click();
          setMapVisibleMemo(false);
          return clearInterval(interval);
        }
      }, 500)
    }
  }, [keys])

  const renderPolylines = async (map, maps) => {
    let geodesicPolyline = new maps.Polyline({
      path: coordinates,
      strokeColor: "#007B5F",
      strokeOpacity: 1.0,
      strokeWeight: 2,
    })
    geodesicPolyline.setMap(map)
  }

  const getNextStop = (stopData) => {
    const index = orderedStops.findIndex((item) => JSON.stringify(stopData) === JSON.stringify(item[1].stop_data));
    return orderedStops.length > index + 1 ? orderedStops[index + 1][1].stop_data.stopName : null;
  }

  const getDatapoint = (stopKey, isClass = false) => {
    const stopObjKey = orderedStops[stopKey][0];
    if (JSON.stringify(data?.stop_markers[direction][stopObjKey]) === JSON.stringify(orderedStops[0][1])) {
      return isClass ? styles.startPoint : 'Start';
    } else if (JSON.stringify(data?.stop_markers[direction][stopObjKey]) === JSON.stringify(orderedStops[orderedStops.length - 1][1])) {
      return isClass ? styles.endPoint : 'End';
    } else {
      if (data?.stop_markers[direction][stopObjKey].stop_data.timepoint === '1') {
        return isClass ? styles.dataTimepoint : '';
      }
      return isClass ? styles.datapoint : '';
    }
  }

  React.useEffect(() => {
    const apiUrl = document.getElementById('ict-routes-react-app').dataset.apiUrl;
    setLoading(true);
    if (mapVisible) {
      setMapVisibleMemo(true);
    }
    setMapVisible(false);
    getData(apiUrl);
  }, [direction])

  React.useEffect(() => {
    if (!data || !Object.keys(data).length) {
      const apiUrl = document.getElementById('ict-routes-react-app').dataset.apiUrl;
      setLoading(true);
      getData(apiUrl);
    }
  }, [view])

  React.useEffect(() => {
    const intervalId = setInterval(() => {
      const apiUrl = document.getElementById('ict-routes-react-app').dataset.apiUrl;
      getData(apiUrl);
    }, 30000)
    return () => clearInterval(intervalId);
  }, [direction, view])

  return Object.keys(data).length && !loading ? (
    <>
      <div className={styles.mapContainer} id="map-container">
        <Accordion className={styles.mapAccordion} flush>
          <Card id="mapAccordionHeader" className={styles.mapAccordionHeader}>
            <Card.Header onClick={() => setMapVisible(!mapVisible)} style={{ cursor: 'pointer', marginBottom: '10px' }}>
              <CustomToggle eventKey={0}>
                <img src={chevronDown} style={mapVisible ? { transform: 'rotate(180deg)' } : { transform: 'rotate(0deg)' }} alt="Toggle Map Visiblity" />
              </CustomToggle>
            </Card.Header>
            <Accordion.Collapse className={styles.mapAccordionBody} eventKey={0}>
              <GoogleMapReact
                bootstrapURLKeys={{ key: "AIzaSyDqUxJwPyuHfnatPYCrCwLKjsAi5r7iRPI" }} // TODO: add to .env with prod creds
                defaultCenter={centerAndZoom[0]}
                defaultZoom={centerAndZoom.length > 1 && centerAndZoom[1] || 12}
                onGoogleApiLoaded={({ map, maps }) => renderPolylines(map, maps)}
              >
                {data?.vehicle_position[direction]?.length && data?.vehicle_position[direction].map((vehicle, vIndex) => {

                  return (
                    <OverlayTrigger
                      placement="top"
                      delay={{ show: 150, hide: 300 }}
                      lat={Number(vehicle.latitude)}
                      lng={Number(vehicle.longitude)}
                      overlay={
                        <Tooltip className={styles.tooltipVehicle}>
                          Bus {vehicle.vehicle_id}
                        </Tooltip>
                      }
                    >
                      <img style={{ height: 50, zIndex: 10, position: 'absolute', transform: 'translate(-50%, -100%)' }} src={(vehicle?.bearing > 180 && vehicle?.bearing <= 360) ? busWestward : busEastward} alt="Bus Indicator" />
                    </OverlayTrigger>
                  )
                })}
                {orderedStops.map((stop, markersIndex) => {
                  const stopData = stop[1];
                  return keys.length && data?.stop_markers[direction][stop[0]] && (
                    <OverlayTrigger
                      placement="top"
                      delay={{ show: 150, hide: 300 }}
                      lat={Number(stopData?.stop_data?.stopLat)}
                      lng={Number(stopData?.stop_data?.stopLon)}
                      overlay={
                        <Tooltip className={styles.toolTipMap}>
                          <button className={styles.closeButton}>x</button>
                          <div>
                            <h4>{stopData.stop_data?.stopName} - Stop {stopData.stop_data?.stopId}</h4>
                            {getNextStop(stopData?.stop_data) && <div><strong>Headed to {getNextStop(stopData?.stop_data)}</strong></div>}
                            {stopData?.real_time[0]?.vehicle_label ?
                              <Table className="d-table" striped bordered hover responsive>
                                <thead>
                                  <tr>
                                    <th className="col-2">Bus</th>
                                    <th className="col-3">EDT</th>
                                    <th className="col-7">Status</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td className="col-2">{stopData?.real_time[Object.keys(stopData?.real_time)[0]]?.vehicle_label}</td>
                                    <td className="col-3">{DateTime.fromMillis(Number(stopData?.real_time[Object.keys(stopData?.real_time)[0]]?.departure_time) * 1000).toLocal().toFormat('h:mm a').replace(' AM', ' a.m.').replace(' PM', ' p.m.')}</td>
                                    <td className="col-7">{
                                      Number(stopData?.real_time[0]?.departure_delay) > 60 ? (
                                        <div className={styles.datapointLate}>
                                          Late
                                        </div>
                                      ) : Number(stopData?.real_time[0]?.departure_delay) < -60
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
                                  {Object.keys(stopData?.real_time)[1] && (
                                    <tr>
                                      <td className="col-2">{stopData?.real_time[Object.keys(stopData?.real_time)[1]]?.vehicle_label}</td>
                                      <td className="col-3">{DateTime.fromMillis(Number(stopData?.real_time[Object.keys(stopData?.real_time)[1]]?.departure_time) * 1000).toLocal().toFormat('h:mm a').replace(' AM', ' a.m.').replace(' PM', ' p.m.')}</td>
                                      <td className="col-7">{
                                        Number(stopData?.real_time[Object.keys(stopData?.real_time)[1]]?.departure_delay) > 60 ? (
                                          <div className={styles.datapointLate}>
                                            Late
                                          </div>
                                        ) : Number(stopData?.real_time[Object.keys(stopData?.real_time)[1]]?.departure_delay) < -60
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
                                  )}
                                  {Object.keys(stopData?.real_time)[2] && (
                                    <tr>
                                      <td className="col-2">{stopData?.real_time[Object.keys(stopData?.real_time)[2]]?.vehicle_label}</td>
                                      <td className="col-3">{DateTime.fromMillis(Number(stopData?.real_time[Object.keys(stopData?.real_time)[2]]?.departure_time) * 1000).toLocal().toFormat('h:mm a').replace(' AM', ' a.m.').replace(' PM', ' p.m.')}</td>
                                      <td className="col-7">{
                                        Number(stopData?.real_time[Object.keys(stopData?.real_time)[2]]?.departure_delay) > 60 ? (
                                          <div className={styles.datapointLate}>
                                            Late
                                          </div>
                                        ) : Number(stopData?.real_time[Object.keys(stopData?.real_time)[2]]?.departure_delay) < -60
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
                                  )}
                                </tbody>
                              </Table>
                              : !getNextStop(data?.stop_markers[direction][stop[0]].stop_data) && <h4 className="text-center">End of the Line</h4>}
                          </div>
                        </Tooltip>
                      }
                    >
                      <div className={getDatapoint(markersIndex, true)}
                      >
                        {getDatapoint(markersIndex)}
                      </div>
                    </OverlayTrigger>
                  )
                })}
              </GoogleMapReact>
            </Accordion.Collapse>
          </Card>
        </Accordion>
      </div>
      <Container className={styles.routesWrapper}>
        <Row className={styles.routesRow}>
          <Col className="order-5 order-xl-1 px-0" xl="8" xs="12">
            <Row className={styles.noMarginsOnMobile}>
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
                      <Image src={info} alt="More information on Timepoints" />
                    </div>
                  </OverlayTrigger>
                </Col>
                <Col className={styles.growOnMobile}>
                  <div className="w-100"><strong className={styles.strong}>Direction:</strong></div>
                  <Row className={styles.formCheckRow}>
                    <Form.Check
                      type="radio"
                      label="Outbound"
                      aria-label="Outbound"
                      value="outbound"
                      onClick={() => setDirection('outbound')}
                      checked={direction === 'outbound'}
                      className={styles.formCheck}
                    />
                    <Form.Check
                      type="radio"
                      label="Inbound"
                      aria-label="Inbound"
                      value="inbound"
                      onClick={() => setDirection('inbound')}
                      checked={direction === 'inbound'}
                      className={styles.formCheckLeft}
                    />
                  </Row>
                </Col>
                <Col className={styles.growOnMobile} style={{ minWidth: '300px' }}>
                  <div className="w-100"><strong className={styles.strong}>View Next Departure As:</strong></div>
                  <Row className={styles.formCheckRow}>
                    <Form.Check
                      type="radio"
                      label="Minutes to Wait"
                      aria-label="Minutes to Wait"
                      value="wait"
                      onClick={() => setView('wait')}
                      checked={view === 'wait'}
                      className={styles.formCheckLeft}
                    />
                    <Form.Check
                      type="radio"
                      label="Departure Time"
                      aria-label="Departure Time"
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
                {new Date().getDay() === (0 || 6) && data.short_name === '42'
                  ? (<h3 style={{ textAlign: 'center' }}>Route 42 does not run on weekends.</h3>)
                  : orderedStops.map((stopKey, stopIndex) => {
                    if (data?.stop_markers[direction][stopKey[0]]) {
                      const stopObj = data?.stop_markers[direction][stopKey[0]];
                      const now = DateTime.now().toMillis();
                      if (stopObj && Object.keys(stopObj).length > 0) {
                        const isTimepoint = Number(data?.stop_markers[direction][stopKey[0]].stop_data.timepoint) > 0;
                        const rtData = data?.stop_markers[direction][stopKey[0]]?.real_time;
                        const rtObjKeys = Object.keys(data?.stop_markers[direction][stopKey[0]]?.real_time);
                        const isLastEmptyStop = rtObjKeys.length === 0;
                        let sortedTimes = [];
                        rtObjKeys.forEach((key) => {
                          if (rtData[key]?.departure_time * 1000 > now) {
                            sortedTimes.push(key);
                          }
                        })
                        sortedTimes.sort((a, b) => {
                          return Number(rtData[a]?.departure_time) - Number(rtData[b]?.departure_time);
                        });
                        let sortedStopTimes = [];
                        const stopTimesCollection = data?.stop_markers[direction][stopKey[0]]?.stop_times.sort((a, b) => new DateTime(a) - new DateTime(b));
                        stopTimesCollection.forEach((item) => {
                          const exploded = item.split(':')
                          const newDate = new Date();
                          newDate.setHours(exploded[0]);
                          newDate.setMinutes(exploded[1]);
                          newDate.setSeconds(exploded[2]);
                          if (newDate.getTime() > now) {
                            sortedStopTimes.push(newDate.getTime());
                          }
                        })
                        if (sortedStopTimes.length === 0) {
                          stopTimesCollection.forEach((item) => {
                            const currentTime = new Date();
                            const exploded = item.split(':')
                            const newDate = new Date();
                            newDate.setDate(currentTime.getDate() + 1);
                            newDate.setHours(exploded[0]);
                            newDate.setMinutes(exploded[1]);
                            newDate.setSeconds(exploded[2]);
                            if (newDate.getTime() > now) {
                              sortedStopTimes.push(newDate.getTime());
                            }
                          })
                        }
                        sortedStopTimes.sort((a, b) => a - b);
                        const delay = sortedTimes.length ? Number(rtData[sortedTimes[0]]?.departure_delay) * 1000 : 0;
                        const delayNext = sortedTimes.length && sortedTimes[1] ? Number(rtData[sortedTimes[1]]?.departure_delay) * 1000 : 0;
                        const delayLast = sortedTimes.length && sortedTimes[2] ? Number(rtData[sortedTimes[2]]?.departure_delay) * 1000 : 0;
                        const departureTimeFormatted = sortedTimes.length ? Number(rtData[sortedTimes[0]]?.departure_time) * 1000 : sortedStopTimes[0];
                        const departureTimeFormattedNext = sortedTimes.length && sortedTimes[1] ? Number(rtData[sortedTimes[1]]?.departure_time) * 1000 : sortedStopTimes[1];
                        const departureTimeFormattedLast = sortedTimes.length && sortedTimes[2] ? Number(rtData[sortedTimes[2]]?.departure_time) * 1000 : sortedStopTimes[2];
                        const waitTime = departureTimeFormatted && ((departureTimeFormatted - DateTime.now().toMillis()) / 60000);
                        const waitTimeNext = departureTimeFormattedNext && ((departureTimeFormattedNext - DateTime.now().toMillis()) / 60000);
                        const waitTimeLast = departureTimeFormattedLast && ((departureTimeFormattedLast - DateTime.now().toMillis()) / 60000);
                        const waitTimeString = (waitTime < 60 && waitTime > -60) ? `${Math.floor(waitTime)} min` : `${Math.floor(waitTime / 60)} hr ${Math.floor(waitTime % 60)} min`;
                        const waitTimeStringNext = (waitTimeNext < 60 && waitTimeNext > -60) ? `${Math.floor(waitTimeNext)} min` : `${Math.floor(waitTimeNext / 60)} hr ${Math.floor(waitTimeNext % 60)} min`;
                        const waitTimeStringLast = (waitTimeLast < 60 && waitTimeLast > -60) ? `${Math.floor(waitTimeLast)} min` : `${Math.floor(waitTimeLast / 60)} hr ${Math.floor(waitTimeLast % 60)} min`;
                        return (
                          <div className={(!isTimepoint && nonTimepointsHidden) ? styles.unmountedStyle : styles.mountedStyle} >
                            <Row className={isTimepoint ? styles.timepoint : styles.stopInfo}>
                              <Col md={isTimepoint && "5"} lg="5" className={!isTimepoint ? styles.stopCol : ''} key={`stopName-${stopIndex}`}>
                                {isTimepoint ? <div className={styles.timepointMarker}>Timepoint</div> : <div class={styles.dot} />}
                                <div className={isTimepoint ? styles.timepointInfo : styles.nonTimepointInfo}><span className={!isTimepoint ? styles.stopText : ''}>{stopObj?.stop_data.stopName}</span> {isTimepoint && <span className={styles.estimated}>Estimated</span>}</div>
                              </Col>
                              {!isLastEmptyStop ? <Col md={isTimepoint && "7"} lg="7" className={isTimepoint ? styles.timepointRight : styles.right}>
                                {waitTime && (
                                  <div className={
                                    delay >= 60
                                      ? styles.lateArrivalTag
                                      : delay <= -60
                                        ? styles.earlyArrivalTag
                                        : styles.arrivalTag}>{view === 'wait' ? <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delay >= 60 ? circleExclamation : delay <= -60 ? alarmClock : circleCheck} /><span>{waitTimeString}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delay >= 60 ? symbolRed : delay <= -60 ? symbolPurple : symbolGreen} /></> : <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delay >= 60 ? circleExclamation : delay <= -60 ? alarmClock : circleCheck} /><span>{DateTime.fromMillis(departureTimeFormatted).toFormat('h:mm a').replace('AM', 'a.m.').replace('PM', 'p.m.')}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delay >= 60 ? symbolRed : delay <= -60 ? symbolPurple : symbolGreen} /></>}
                                  </div>
                                )}
                                {waitTimeNext && (
                                  <div className={
                                    delayNext >= 60
                                      ? styles.lateArrivalTag
                                      : delayNext <= -60
                                        ? styles.earlyArrivalTag
                                        : styles.arrivalTag}>{view === 'wait' ? <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delayNext >= 60 ? circleExclamation : delayNext <= -60 ? alarmClock : circleCheck} /><span>{waitTimeStringNext}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delayNext >= 60 ? symbolRed : delayNext <= -60 ? symbolPurple : symbolGreen} /></> : <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delayNext >= 60 ? circleExclamation : delayNext <= -60 ? alarmClock : circleCheck} /><span>{DateTime.fromMillis(departureTimeFormattedNext).toFormat('h:mm a').replace('AM', 'a.m.').replace('PM', 'p.m.')}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delayNext >= 60 ? symbolRed : delayNext <= -60 ? symbolPurple : symbolGreen} /></>}
                                  </div>
                                )}
                                {waitTimeLast && (
                                  <div className={
                                    delayLast >= 60
                                      ? styles.lateArrivalTag
                                      : delayLast <= -60
                                        ? styles.earlyArrivalTag
                                        : styles.arrivalTag}>{view === 'wait' ? <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delayLast >= 60 ? circleExclamation : delayLast <= -60 ? alarmClock : circleCheck} /><span>{waitTimeStringLast}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delayLast >= 60 ? symbolRed : delayLast <= -60 ? symbolPurple : symbolGreen} /></> : <><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.indicator} src={delayLast >= 60 ? circleExclamation : delayLast <= -60 ? alarmClock : circleCheck} /><span>{DateTime.fromMillis(departureTimeFormattedLast).toFormat('h:mm a').replace('AM', 'a.m.').replace('PM', 'p.m.')}</span><Image alt={delay >= 60 ? 'Late' : delay <= -60 ? 'Early' : 'OnTime'} className={styles.shape} src={delayLast >= 60 ? symbolRed : delayLast <= -60 ? symbolPurple : symbolGreen} /></>}
                                  </div>
                                )}
                            </Col> : <Col md={isTimepoint && "7"} lg="7" className={isTimepoint ? styles.timepointRight : styles.right}>Continues {direction === 'inbound' ? 'outbound' : 'inbound'}</Col>}
                            </Row>
                            {isTimepoint && orderedStops[stopIndex + 1]
                              ? (
                                <>
                                  <div class={styles.empty} />
                                  <div className={nonTimepointsHidden ? styles.show : styles.hide} onClick={(e) => {
                                    e.preventDefault();
                                    setNonTimepointsHidden(!nonTimepointsHidden);
                                  }}>
                                    <div class={styles.dot} />
                                    <div class="d-inline-block">{nonTimepointsHidden ? 'Show' : 'Hide'} Non-Timepoint Stops</div>
                                  </div>
                                  {nonTimepointsHidden && <div class={styles.empty} />}
                                </>
                              ) : null}
                          </div>
                        )
                      }
                    }
                  })}
              </Col>
            </Row>
          </Col>
          <Col className="order-1 order-xl-5 px-0" id="legend" xs="12" md="9" xl="4">
            <div className={styles.stickyWrapper}>
              <div className={styles.void} />
              <div className={styles.legend}>
                <div className={styles.legendTitle}>Departures Info &amp; Legend</div>
                <div className="d-flex mb-3">
                  <div style={{ marginLeft: 0, display: 'inline' }} className={styles.arrivalTag}><Image alt="On Time" className={styles.indicator} src={circleCheck} />On Time<Image alt="On Time" className={styles.shape} src={symbolGreen} /></div>
                  <div style={{ marginLeft: '10px', display: 'inline' }} className={styles.earlyArrivalTag}><Image alt="Early" className={styles.indicator} src={alarmClock} />Early<Image alt="Early" className={styles.shape} src={symbolPurple} /></div>
                  <div style={{ marginLeft: '10px', display: 'inline' }} className={styles.lateArrivalTag}><Image alt="Late" className={styles.indicator} src={circleExclamation} />Late<Image alt="Late" className={styles.shape} src={symbolRed} /></div>
                </div>
                <div className={styles.legendText}>
                  <div className="mb-4">Estimated departure times are based on real-time data.</div>
                  <div>The times listed correspond to a bus that is currently on this route.</div>
                </div>
              </div>
            </div>
          </Col>
        </Row>
      </Container>
    </>
  ) : <div className="mt-5 text-center" aria-busy="true"><h2>Loading Real-Time Information...</h2><Spinner style={{ width: '10rem', height: '10rem' }} className={styles.spinner} variant="success" /></div>
}

export default RealTimeDepartures;
