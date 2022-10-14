
// @ts-expect-error
const general: generalTextType = window.breezeTxtGeneral ?? Buffer.from(process.env.REACT_APP_DEV_TEXT, 'base64')
// @ts-expect-error
const mood: moodTextType = window.breezeTxtMood ?? Buffer.from(process.env.REACT_APP_DEV_TEXT_MOOD, 'base64')
// @ts-expect-error
const like: likeTextType = window.breezeTxtLike ?? Buffer.from(process.env.REACT_APP_DEV_TEXT_LIKE, 'base64')

const smfTextVars = {
  general,
  mood,
  like
}

export default smfTextVars
