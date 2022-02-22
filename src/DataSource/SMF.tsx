
// @ts-ignore
const session = window.smf_session_var || {
	var: process.env['REACT_APP_DEV_SESSION_VAR '],
	id: process.env['REACT_APP_DEV_SESSION_ID']
}
// @ts-ignore
const youSure = window.smf_you_sure || function(){ return true}
// @ts-ignore
const ajaxIndicator = window.ajax_indicator || false
// @ts-ignore
const scriptUrl = window.smf_scripturl || process.env['REACT_APP_DEV_URL']
// @ts-ignore
const txt = window.breezeTxtGeneral || []
// @ts-ignore
const userId = window.smf_member_id || 0

const smfVars = {
	session: session,
	youSure: youSure,
	ajaxIndicator: ajaxIndicator,
	txt: txt,
	scriptUrl: scriptUrl,
	userId: userId
}

export default smfVars

