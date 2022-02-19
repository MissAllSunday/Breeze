declare module 'breezeTypes' {
	type smfVars = {
		session: {
			var:string,
			id:string
		},
		youSure: string,
		ajaxIndicator: boolean,
		txt: {
			moodTxt: object
		},
		scriptUrl: string,
		userId: number
	};

	interface appProps {
		smfVars: smfVars,
	}
}

module.exports = {
	smfVars,
	appProps,
};
