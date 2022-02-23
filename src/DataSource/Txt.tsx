
// @ts-ignore
const general = window.breezeTxtGeneral || atob(process.env['REACT_APP_DEV_TEXT'])
// @ts-ignore
const mood = window.breezeTxtMood || atob(process.env['REACT_APP_DEV_TEXT_MOOD'])
// @ts-ignore
const like = window.breezeTxtLike || atob(process.env['REACT_APP_DEV_TEXT_LIKE'])


const smfVars = {
	general: general,
	mood: mood,
	like: like,
}

export default smfVars

