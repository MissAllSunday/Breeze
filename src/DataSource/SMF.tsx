
// @ts-ignore
const session = window.smf_session_var || {
	var: process.env['REACT_APP_DEV_SESSION_VAR'],
	id: process.env['REACT_APP_DEV_SESSION_ID']
}
// @ts-ignore
const youSure = window.smf_you_sure || function(){ return true}
// @ts-ignore
const ajaxIndicator = window.ajax_indicator || false
// @ts-ignore
const scriptUrl = window.smf_scripturl || process.env['REACT_APP_DEV_URL']
// @ts-ignore
const userId = parseInt(window.smf_member_id || process.env['REACT_APP_DEV_USER_ID'])
// @ts-ignore
const wallId = parseInt((typeof breezeUsers === 'undefined' || breezeUsers === null) ? process.env['REACT_APP_DEV_WALL_ID'] : breezeUsers.wallOwner)
// @ts-ignore
const ownerSettings = window.breezeProfileOwnerSettings || atob(process.env['REACT_APP_DEV_OWNER_SETTINGS'])
// @ts-ignore
const isCurrentUserOwner = Boolean(window.breezeIsCurrentUserOwner || process.env['REACT_APP_DEV_IS_CURRENT_USER_OWNER'])
// @ts-ignore
const useMood = Boolean(window.breezeUseMood || process.env['REACT_APP_DEV_USE_MOOD'])

const smfVars = {
	session: session,
	youSure: youSure,
	ajaxIndicator: ajaxIndicator,
	scriptUrl: scriptUrl,
	userId: userId,
	wallId,
	ownerSettings,
	isCurrentUserOwner,
	useMood,
}

export default smfVars

