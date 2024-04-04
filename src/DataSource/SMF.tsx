const session = {
  // @ts-expect-error SMF external variable
  var: window.smf_session_var ?? process.env.REACT_APP_DEV_SESSION_VAR,
  // @ts-expect-error SMF external variable
  id: window.smf_session_id ?? process.env.REACT_APP_DEV_SESSION_ID,
};
// @ts-expect-error SMF external variable
const youSure = window.smf_you_sure ?? '';
// @ts-expect-error SMF external variable
const ajaxIndicator = window.ajax_indicator ?? false;
// @ts-expect-error SMF external variable
const scriptUrl = window.smf_scripturl ?? process.env.REACT_APP_DEV_URL;
// @ts-expect-error SMF external variable
const userId = parseInt(window.smf_member_id ?? process.env.REACT_APP_DEV_USER_ID);
// @ts-expect-error Backend variable
const wallId = parseInt(window.breezeProfileId ?? process.env.REACT_APP_DEV_WALL_ID);
// @ts-expect-error Backend variable
const isCurrentUserOwner = Boolean(window.breezeIsCurrentUserOwner ?? process.env.REACT_APP_DEV_IS_CURRENT_USER_OWNER);
// @ts-expect-error SMF variable
const smfImagesUrl: string = window.smf_images_url ?? process.env.REACT_APP_DEV_THEME_URL;
// @ts-expect-error Backend variable
const pagination: number = window.breezePagination ?? process.env.REACT_APP_DEV_THEME_URL;
// @ts-expect-error Backend variable
const editorId: string = window.breezeEditorId ?? 'Breeze';

const smfVars = {
  session,
  youSure,
  ajaxIndicator,
  scriptUrl,
  userId,
  wallId,
  isCurrentUserOwner,
  smfImagesUrl,
  pagination,
  editorId,
};

export default smfVars;
