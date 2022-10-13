
// @ts-expect-error
const general: generalTextType = window.breezeTxtGeneral || atob(process.env.REACT_APP_DEV_TEXT)
// @ts-expect-error
const mood: moodTextType = window.breezeTxtMood || atob(process.env.REACT_APP_DEV_TEXT_MOOD)
// @ts-expect-error
const like: likeTextType = window.breezeTxtLike || atob(process.env.REACT_APP_DEV_TEXT_LIKE)

const smfTextVars = {
  general,
  mood,
  like
}

export default smfTextVars
