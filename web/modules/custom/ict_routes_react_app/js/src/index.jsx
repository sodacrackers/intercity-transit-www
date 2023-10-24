import * as React from 'react';
import * as ReactDOM from 'react-dom';
import RealTimeArrivals from './components/RealTimeArrivals/index';
import './index.css';

import 'bootstrap/dist/css/bootstrap.min.css';

ReactDOM.render(
  <RealTimeArrivals />,
  document.getElementById('ict-routes-react-app')
);