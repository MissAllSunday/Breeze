import axios from "axios";
import { UtilsProps } from "breezeTypes";
import toast, { Toaster } from 'react-hot-toast';
import { ToastCallback } from "breezeTypes";
import { NoticeOptions } from "breezeTypes";

export default class Utils {
	static api = axios;
	private static props: UtilsProps;

	constructor(props: UtilsProps) {

	}

	static setNotice(options:NoticeOptions, onCloseCallback: ToastCallback) {
		toast.custom("<div class='infobox'>Hello World</div>");
	}

	static clearNotice() {
		toast.dismiss();
	}

	static sprintFormat(arrayArguments:Array<string>)
	{
		let baseUrl = this.props.smfVars.scriptUrl +
			'?action={0};' + this.props.smfVars.session.var +'='+ this.props.smfVars.session.id + ';sa={1}';
		let i = arrayArguments.length

		while (i--) {
			baseUrl = baseUrl.replace(new RegExp('\\{' + i + '\\}', 'gm'), arrayArguments[i]);
		}

		return baseUrl;
	}

    private static canUseLocalStorage()
	{
        let storage = window['localStorage']

		try {
			let	x = 'breeze_storage_test';
			storage.setItem(x, x);
			storage.removeItem(x);

			return true;
		}
		catch(e) {
			return e instanceof DOMException && (
					e.code === 22 ||
					e.code === 1014 ||
					e.name === 'QuotaExceededError' ||
					e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
				storage && storage.length !== 0;
		}
	}

    setLocalObject(keyName:string, objectToStore:object)
	{
		if (!Utils.canUseLocalStorage()) {
		return false;
		}

		localStorage.setItem(keyName, JSON.stringify(objectToStore));

		return true;
	}

	getLocalObject(keyName:string)
	{
		if (!Utils.canUseLocalStorage()) {
			return false;
		}

    	let objectStored = JSON.parse(<string>localStorage.getItem(keyName));

		if (objectStored !== null){
			return objectStored;
		} else {
			return false;
		}
	}

    decode(html:string)
	{
		let decoder = document.createElement('div');
		decoder.innerHTML = html;
		return decoder.textContent;
	}
}
