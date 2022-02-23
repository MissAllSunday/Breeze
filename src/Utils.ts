import axios from 'axios';
import Smf from './DataSource/SMF';

const api = () => {
	return axios
}

const buildBaseUrlWithParams = () =>
{
	let smfVars = Smf
	let baseUrlWithParams = new URL(smfVars.scriptUrl)

	baseUrlWithParams.searchParams.append(smfVars.session.var, smfVars.session.id)

	return baseUrlWithParams;
}

const canUseLocalStorage = () =>
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

const setLocalObject = (keyName:string, objectToStore:object) =>
{
	if (!canUseLocalStorage()) {
		return false;
	}

	localStorage.setItem(keyName, JSON.stringify(objectToStore));

	return true;
}

const getLocalObject = (keyName:string) =>
{
	if (!canUseLocalStorage()) {
		return false;
	}

	let objectStored = JSON.parse(localStorage.getItem(keyName) as string);

	if (objectStored !== null) {
		return objectStored;
	} else {
		return false;
	}
}

const decode = (html:string) =>
{
	let decoder = document.createElement('div');
	decoder.innerHTML = html;
	return decoder.textContent;
}


export default {
	decode,
	getLocalObject,
	setLocalObject,
	canUseLocalStorage,
	buildBaseUrlWithParams,
	api
}
