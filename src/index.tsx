import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';

let smfVars = {
	session: {
		// @ts-ignore
		var: window.smf_session_var || '',
		// @ts-ignore
		id: window.smf_session_id || ''
	},
	// @ts-ignore
	youSure: smf_you_sure,
	// @ts-ignore
	ajaxIndicator: ajax_indicator || undefined,
	// @ts-ignore
	txt: window.breezeTxtGeneral || undefined,

	scriptUrl: window.smf_scripturl
}

ReactDOM.render(
  <React.StrictMode>
    <App
	smfVars={smfVars}/>
  </React.StrictMode>,
  document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
