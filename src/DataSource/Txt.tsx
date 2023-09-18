
// @ts-expect-error Backend text variable
const general: generalTextType = window.breezeTxtGeneral ?? process.env.REACT_APP_DEV_TEXT
// @ts-expect-error Backend text variable
const like: likeTextType = window.breezeTxtLike ?? process.env.REACT_APP_DEV_TEXT_LIKE
// @ts-expect-error Backend text variable
const error: errorTextType = window.breezeTxtError ?? process.env.REACT_APP_DEV_TEXT_LIKE

const smfTextVars = {
  general,
  like,
  error
}

export default smfTextVars
