
// @ts-ignore
const general:generalTextType = window.breezeTxtGeneral || atob(process.env['REACT_APP_DEV_TEXT'])
// @ts-ignore
const mood:moodTextType = window.breezeTxtMood || atob(process.env['REACT_APP_DEV_TEXT_MOOD'])
// @ts-ignore
const like:likeTextType = window.breezeTxtLike || atob(process.env['REACT_APP_DEV_TEXT_LIKE'])


const smfTextVars = {
	general: general,
	mood: mood,
	like: like,
}

export default smfTextVars

