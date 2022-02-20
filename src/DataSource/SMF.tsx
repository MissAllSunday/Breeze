import { smfVarsType } from 'breezeTypes';
import {useState} from "react";

export default function  Smf(): smfVarsType {
	// @ts-ignore
	const [sessionData, setSessionData] = useState(window.smf_session_var || [])
	// @ts-ignore
	const [youSure, setYouSure] = useState(smf_you_sure || function(){ return true})
	// @ts-ignore
	const [ajaxIndicator, setAjaxIndicator] = useState(ajax_indicator || false,)
	// @ts-ignore
	const [scriptUrl, setScriptUrl] = useState(window.smf_scripturl || process.env["REACT_APP_DEV_URL"])
	// @ts-ignore
	const [txt, setTxt] = useState(window.breezeTxtGeneral || [])
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
