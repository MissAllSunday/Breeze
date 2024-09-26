import * as breezeTypesText from 'breezeTypesText';

// @ts-expect-error Backend text variable
const general: GeneralTextType = window.breezeTxtGeneral ?? JSON.parse(process.env.REACT_APP_DEV_TEXT);
// @ts-expect-error Backend text variable
const like: LikeTextType = window.breezeTxtLike ?? JSON.parse(process.env.REACT_APP_DEV_TEXT_LIKE);
// @ts-expect-error Backend text variable
const error: ErrorTextType = window.breezeTxtError ?? JSON.parse(process.env.REACT_APP_DEV_TEXT_ERROR);
// @ts-expect-error Backend text variable
const tabs: TabsTextType = window.breezeTxtTabs ?? JSON.parse(process.env.REACT_APP_DEV_TEXT_TABS);

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
