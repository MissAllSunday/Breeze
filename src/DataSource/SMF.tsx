// @ts-ignore

const session = {
	// @ts-expect-error SMF external variable
	var: window.smf_session_var ?? process.env.VITE_APP_DEV_SESSION_VAR,
	// @ts-expect-error SMF external variable
	id: window.smf_session_id ?? process.env.VITE_APP_DEV_SESSION_ID,
};
// @ts-expect-error SMF external variable
const youSure = window.smf_you_sure ?? "";
// @ts-expect-error SMF external variable
const ajaxIndicator = window.ajax_indicator ?? false;
// @ts-expect-error SMF external variable
const script_url = window.smf_scripturl ?? process.env.VITE_APP_DEV_URL;

// eslint-disable-next-line no-console
console.log("[SMF Debug] script_url:", script_url);

const user_id = parseInt(
	window.smf_member_id ?? process.env.VITE_APP_DEV_USER_ID,
);

const wall_id = parseInt(
	window.breezeProfileId ?? process.env.VITE_APP_DEV_WALL_ID,
);
const isCurrentUserOwner = Boolean(
	window.breezeIsCurrentUserOwner ??
		process.env.VITE_APP_DEV_IS_CURRENT_USER_OWNER,
);
// @ts-expect-error settings are loaded server side
const canShowAddBuddyButton: boolean =
	Boolean(window.breezeCanShowAddBuddyButton) ?? false;
// @ts-expect-error SMF variable
const smf_images_url: string =
	window.smf_images_url ?? process.env.VITE_APP_DEV_THEME_URL;
// @ts-expect-error Backend variable
const pagination: number =
	window.breezePagination ?? process.env.VITE_APP_DEV_THEME_URL;

// @ts-expect-error editor gets defined serverside
const smfEditorHandler = window.sceditor ?? null;

const aboutMe =
	document.getElementById("tab-about") ?? document.createElement("tab-about");
const aboutMeContent = aboutMe.innerHTML;
aboutMe.innerHTML = "";
const buddiesTab =
	document.getElementById("tab-buddies") ??
	document.createElement("tab-buddies");
const buddiesTabContent = buddiesTab.innerHTML;
buddiesTab.innerHTML = "";

// @ts-expect-error editor gets defined serverside
const editorOptions = window.breezeEditorOptions || [];
// @ts-expect-error editor gets defined serverside
const editorIsRich = window.breezeEditorIsRich || false;
// @ts-expect-error editor gets defined serverside
const currentUserAvatar =
	window.breezeCurrentUserAvatar || `${window.smf_avatars_url}/default.png`;

const csrfToken = {
	// @ts-expect-error CSRF token var name set serverside
	var: window.breezeCsrfTokenVar ?? "",
	// @ts-expect-error CSRF token value set serverside
	value: window.breezeCsrfTokenValue ?? "",
};

const smfVars = {
	session,
	youSure,
	ajaxIndicator,
	script_url,
	user_id,
	wall_id,
	isCurrentUserOwner,
	canShowAddBuddyButton,
	smf_images_url,
	pagination,
	aboutMeContent,
	buddiesTabContent,
	smfEditorHandler,
	editorOptions,
	editorIsRich,
	currentUserAvatar,
	csrfToken,
};

export default smfVars;

// rawEditorElement.innerHTML = '';
