declare module 'breezeTypes' {
	type smfVars = {
		session: {
			var:string,
			id:string
		},
		youSure: object,
		ajaxIndicator: boolean,
		txt: object
	};

	interface appProps {
		smfVars: smfVars,
	}
}

module.exports = {
	smfVars,
	appProps,
};
