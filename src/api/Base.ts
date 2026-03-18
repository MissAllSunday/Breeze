import SmfVars from "../DataSource/SMF";

export const updateCsrfToken = (token: {
	var: string;
	value: string;
}): void => {
	SmfVars.csrfToken.var = token.var;
	SmfVars.csrfToken.value = token.value;
};

export const baseConfig = (params: object = {}): object => ({
	data: params,
	headers: {
		"X-SMF-AJAX": "1",
	},
});

export const baseUrl = (
	action: string,
	subAction: string,
	additionalParams: object[] = [],
): string => {
	const url = new URL(SmfVars.script_url);

	url.searchParams.append("action", action);
	url.searchParams.append("sa", subAction);
	url.searchParams.append(SmfVars.session.var, SmfVars.session.id);

	if (SmfVars.csrfToken.var && SmfVars.csrfToken.value) {
		url.searchParams.append(SmfVars.csrfToken.var, SmfVars.csrfToken.value);
	}

	additionalParams.map((objectValue): null => {
		for (const [key, value] of Object.entries(objectValue)) {
			url.searchParams.append(key, value);
		}

		return null;
	});

	return url.href;
};
