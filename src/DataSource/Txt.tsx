
// @ts-expect-error
const general: generalTextType = window.breezeTxtGeneral ?? process.env.REACT_APP_DEV_TEXT
// @ts-expect-error
const like: likeTextType = window.breezeTxtLike ?? process.env.REACT_APP_DEV_TEXT_LIKE
// @ts-expect-error
const error: errorTextType = window.breezeTxtError ?? process.env.REACT_APP_DEV_TEXT_LIKE

const smfTextVars = {
  general,
  like,
  error
}

export default smfTextVars
