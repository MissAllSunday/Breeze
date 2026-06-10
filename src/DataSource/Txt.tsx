/// <reference types="vite/client" />
import type * as breezeTypesText from "breezeTypesText";

interface IBreezeWindowInterface extends Window {
	breezeTxtGeneral: breezeTypesText.GeneralTextType;
	breezeTxtLike: breezeTypesText.LikeTextType;
	breezeTxtError: breezeTypesText.ErrorTextType;
	breezeTxtTabs: breezeTypesText.TabsTextType;
	breezeTxtActions: breezeTypesText.ActionsTextType;
}

interface ISmfTextVars {
	general: breezeTypesText.GeneralTextType;
	like: breezeTypesText.LikeTextType;
	error: breezeTypesText.ErrorTextType;
	tabs: breezeTypesText.TabsTextType;
	actions: breezeTypesText.ActionsTextType;
}

const breezeWindow = window as unknown as IBreezeWindowInterface;

// @ts-expect-error Backend text variable
const general: GeneralTextType =
	breezeWindow.breezeTxtGeneral ??
	JSON.parse(import.meta.env.VITE_APP_DEV_TEXT);
// @ts-expect-error Backend text variable
const like: LikeTextType =
	breezeWindow.breezeTxtLike ??
	JSON.parse(import.meta.env.VITE_APP_DEV_TEXT_LIKE);
// @ts-expect-error Backend text variable
const error: ErrorTextType =
	breezeWindow.breezeTxtError ??
	JSON.parse(import.meta.env.VITE_APP_DEV_TEXT_ERROR);
// @ts-expect-error Backend text variable
const tabs: TabsTextType =
	breezeWindow.breezeTxtTabs ??
	JSON.parse(import.meta.env.VITE_APP_DEV_TEXT_TABS);
// @ts-expect-error Backend text variable
const actions: ActionsTextType =
	breezeWindow.breezeTxtActions ??
	JSON.parse(import.meta.env.VITE_APP_DEV_TEXT_ACTIONS);

const smfTextVars: ISmfTextVars = {
	general,
	like,
	error,
	tabs,
	actions,
};

export default smfTextVars;
