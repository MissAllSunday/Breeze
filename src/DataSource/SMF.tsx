import { smfVars } from 'breezeTypes';
import {useState} from "react";

export default function  Smf(): smfVars {
	// @ts-ignore
	const [sessionData, setSessionData] = useState(window.smf_session_var || '')
	// @ts-ignore
	const [youSure, setYouSure] = useState(smf_you_sure)
	// @ts-ignore
	const [ajaxIndicator, setAjaxIndicator] = useState(ajax_indicator || undefined,)
	// @ts-ignore
	const [scriptUrl, setScriptUrl] = useState(window.smf_scripturl || '')
	// @ts-ignore
	const [txt, setTxt] = useState(window.breezeTxtGeneral || undefined)
	// @ts-ignore
	const [userId, setUserId] = useState(window.smf_member_id || 0)

	return {
		session: sessionData,
		youSure: youSure,
		ajaxIndicator: ajaxIndicator,
		txt: txt,
		scriptUrl: scriptUrl,
		userId: userId
	}
}
