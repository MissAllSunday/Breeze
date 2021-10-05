import React, {Component} from 'react';

class Utils extends Component
{
	smfVars = {
		session: {
			// @ts-ignore
			var: window.smf_session_var || '',
			// @ts-ignore
			if: window.smf_session_id || ''
		},
		// @ts-ignore
		youSure: smf_you_sure,
		// @ts-ignore
		ajaxIndicator: ajax_indicator || undefined
	}

	// @ts-ignore
	txt = window.breezeTxtGeneral || undefined

	apiActions = {
		actions: {
			comment: 'breezeComment',
			status: 'breezeStatus',
			mood: 'breezeMood',
			like: 'breezeLike',
		},
		subActions: {
			status: {
				post: 'postStatus',
				byProfile: 'statusByProfile',
				eliminate: 'deleteStatus',
			},
			comment: {
				post: 'postComment',
				eliminate: 'deleteComment',
			},
			mood: {
				all: 'getAllMoods',
				active: 'getActiveMoods',
				eliminate: 'deleteMood',
				post: 'postMood',
				setMood: 'setUserMood'
			},
			like: {
				like: 'like',
				unlike: 'unlike',
			},
		},
	}

	ownConfirm(){
		return confirm(this.smfVars.youSure)
	}

	sprintFormat(stringToGlue: string, arrayArguments: string | any[]) {
		let i = arrayArguments.length

		while (i--) {
			stringToGlue = stringToGlue.replace(new RegExp('\\{' + i + '\\}', 'gm'), arrayArguments[i]);
		}

		return stringToGlue;
	}

	setLoading() {
		this.smfVars.ajaxIndicator(true);
	}
	clearLoading() {
		this.smfVars.ajaxIndicator(false);
	}

	setLocalObject(keyName: string, objectToStore: object){
		if (!this.canUseLocalStorage()) {
			return false;
		}

		localStorage.setItem(keyName, JSON.stringify(objectToStore));

		return true;
	}

	getLocalObject(keyName: string) {
		if (!this.canUseLocalStorage()) {
			return false;
		}

		let objectStored = JSON.parse(localStorage.getItem(keyName) as string);

		if (objectStored !== null){
			return objectStored;
		} else {
			return false;
		}
	}

	canUseLocalStorage() {
		let localStorage = window['localStorage'];

		try {
			let x = 'breeze_storage_test';
			localStorage.setItem(x, x);
			localStorage.removeItem(x);

			return true;
		}
		catch(e) {
			return e instanceof DOMException && (
					e.code === 22 ||
					e.code === 1014 ||
					e.name === 'QuotaExceededError' ||
					e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
				localStorage && localStorage.length !== 0;
		}
	}

	parseMoods(moods: object)
	{

		Object.values(moods).map((mood) => {
			mood.emoji = this.decodeMood(mood.emoji)
			mood.isActive = Boolean(Number(mood.isActive))
			mood.id = Number(mood.id)

			return mood
		});

		return moods;
	}

	parseItem(items: object){
		let selfUtils = this;

		Object.values(items).map(function(item) {
			item.body = selfUtils.decodeMood(item.body)
			item.formatedDate = selfUtils.formatDate(item.createdAt)

			return item
		});

		return items;
	}

	decodeMood(html: string) {
		let decoder = document.createElement('div');
		decoder.innerHTML = html;
		return decoder.textContent;
	}

	formatDate(unixTime: string) {
		return unixTime
	}
}

module.exports = Utils
