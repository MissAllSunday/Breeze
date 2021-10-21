declare module 'breezeTypes' {
	type smfVars = {
		session: {
			var:string,
			id:string
		},
		youSure: string,
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
