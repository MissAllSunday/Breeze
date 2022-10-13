
// @ts-expect-error
const session = window.smf_session_var || {
  var: process.env.REACT_APP_DEV_SESSION_VAR,
  id: process.env.REACT_APP_DEV_SESSION_ID
}
// @ts-expect-error
const youSure = window.smf_you_sure || function () { return true }
// @ts-expect-error
const ajaxIndicator = window.ajax_indicator || false
// @ts-expect-error
const scriptUrl = window.smf_scripturl || process.env.REACT_APP_DEV_URL
// @ts-expect-error
const userId = parseInt(window.smf_member_id || process.env.REACT_APP_DEV_USER_ID)
// @ts-expect-error
const wallId = parseInt((typeof breezeUsers === 'undefined' || breezeUsers === null) ? process.env.REACT_APP_DEV_WALL_ID : breezeUsers.wallOwner)
// @ts-expect-error
const ownerSettings = window.breezeProfileOwnerSettings || atob(process.env.REACT_APP_DEV_OWNER_SETTINGS)
// @ts-expect-error
const isCurrentUserOwner = Boolean(window.breezeIsCurrentUserOwner || process.env.REACT_APP_DEV_IS_CURRENT_USER_OWNER)
// @ts-expect-error
const useMood = Boolean(window.breezeUseMood || process.env.REACT_APP_DEV_USE_MOOD)

const smfVars = {
  session,
  youSure,
  ajaxIndicator,
  scriptUrl,
  userId,
  wallId,
  ownerSettings,
  isCurrentUserOwner,
  useMood
}

export default smfVars
