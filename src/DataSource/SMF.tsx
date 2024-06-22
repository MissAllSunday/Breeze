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
const isCurrentUserOwner = Boolean(window.breezeIsCurrentUserOwner ??
  process.env.REACT_APP_DEV_IS_CURRENT_USER_OWNER);
// @ts-expect-error SMF variable
const smfImagesUrl: string = window.smf_images_url ?? process.env.REACT_APP_DEV_THEME_URL;
// @ts-expect-error Backend variable
const pagination: number = window.breezePagination ?? process.env.REACT_APP_DEV_THEME_URL;

// @ts-expect-error editor gets defined serverside
const smfEditorHandler = window.sceditor ?? null;

const aboutMe = (document.getElementById('tab-about') ??
  document.createElement('tab-about'));
const aboutMeContent = aboutMe.innerHTML;
aboutMe.innerHTML = '';

const editorId = 'Breeze';
const rawEditorElement = (document.getElementById('editor_container') ??
  document.createElement('editor_container'));
const editorElement = rawEditorElement.cloneNode( true ) as HTMLElement;
console.log(editorElement);
rawEditorElement.innerHTML = '';

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
  aboutMeContent,
  smfEditorHandler,
  editorElement,
};

export default smfVars;

// rawEditorElement.innerHTML = '';
