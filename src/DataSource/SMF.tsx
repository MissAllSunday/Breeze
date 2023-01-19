import { Buffer } from 'buffer'

// @ts-expect-error
const session = window.smf_session_var ?? {
  var: process.env.REACT_APP_DEV_SESSION_VAR,
  id: process.env.REACT_APP_DEV_SESSION_ID
}
// @ts-expect-error
const youSure = window.smf_you_sure ?? function () { return true }
// @ts-expect-error
const ajaxIndicator = window.ajax_indicator ?? false
// @ts-expect-error
const scriptUrl = window.smf_scripturl ?? process.env.REACT_APP_DEV_URL
// @ts-expect-error
const userId = parseInt(window.smf_member_id ?? process.env.REACT_APP_DEV_USER_ID)
// @ts-expect-error
const wallId = parseInt(window.breezeWallOwnerID ?? process.env.REACT_APP_DEV_WALL_ID)
// @ts-expect-error
const ownerSettings = window.breezeProfileOwnerSettings ?? Buffer.from(process.env.REACT_APP_DEV_OWNER_SETTINGS, 'base64')
// @ts-expect-error
const isCurrentUserOwner = Boolean(window.breezeIsCurrentUserOwner ?? process.env.REACT_APP_DEV_IS_CURRENT_USER_OWNER)
// @ts-expect-error
const useMood = Boolean(window.breezeUseMood ?? process.env.REACT_APP_DEV_USE_MOOD)
// @ts-expect-error
const smfImagesUrl: string = window.smf_images_url ?? process.env.REACT_APP_DEV_THEME_URL

const smfVars = {
  session,
  youSure,
  ajaxIndicator,
  scriptUrl,
  userId,
  wallId,
  ownerSettings,
  isCurrentUserOwner,
  useMood,
  smfImagesUrl
}

export default smfVars
