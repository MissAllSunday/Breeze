import SmfVars from '../DataSource/SMF';

export const baseUrl = (action: string, subAction:string, additionalParam:Object = []) =>
{
	let baseUrl = new URL(SmfVars.scriptUrl);

	baseUrl.searchParams.append('action', action);
	baseUrl.searchParams.append('sa', subAction);
	baseUrl.searchParams.append('wallId', SmfVars.wallId);

	baseUrl.searchParams.append(SmfVars.session.var, SmfVars.session.id);

	for (const [key, value] of Object.entries(additionalParam)) {
		baseUrl.searchParams.append(key, value);
	}

	return baseUrl.href;
}

export const baseConfig = (params:object = {}) =>
{
	return {
		data: baseParams(params),
		headers: {
			'X-SMF-AJAX': '1'
		}}
}

const baseParams = (params: object) =>
{
	const defaultParams = {
		wallId: SmfVars.wallId
	};

	return {
		...defaultParams,
		...params
	};
}
