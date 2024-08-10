import * as breezeTypesText from 'breezeTypesText';

// @ts-expect-error Backend text variable
const general: GeneralTextType = window.breezeTxtGeneral ?? process.env.REACT_APP_DEV_TEXT;
// @ts-expect-error Backend text variable
const like: LikeTextType = window.breezeTxtLike ?? process.env.REACT_APP_DEV_TEXT_LIKE;
// @ts-expect-error Backend text variable
const error: ErrorTextType = window.breezeTxtError ?? process.env.REACT_APP_DEV_TEXT_LIKE;
// @ts-expect-error Backend text variable
const tabs: TabsTextType = window.breezeTxtTabs ?? process.env.REACT_APP_DEV_TEXT_TABS;

interface ISmfTextVars {
  general: breezeTypesText.GeneralTextType;
  like: breezeTypesText.LikeTextType,
  error: breezeTypesText.ErrorTextType,
  tabs: breezeTypesText.TabsTextType,
}

const smfTextVars: ISmfTextVars = {
  general,
  like,
  error,
  tabs,
};

export default smfTextVars;
