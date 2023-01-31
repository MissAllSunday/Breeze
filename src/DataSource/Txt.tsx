
// @ts-expect-error
const general: generalTextType = window.breezeTxtGeneral ?? Buffer.from(process.env.REACT_APP_DEV_TEXT, 'base64')
// @ts-expect-error
const like: likeTextType = window.breezeTxtLike ?? Buffer.from(process.env.REACT_APP_DEV_TEXT_LIKE, 'base64')

const smfTextVars = {
  general,
  like
}

export default smfTextVars
