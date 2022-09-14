import SmfVars from '../DataSource/SMF';

export const baseUrl = (action: string, subAction:string) =>
{
	let baseUrl = new URL(SmfVars.scriptUrl);

	baseUrl.searchParams.append('action', action);
	baseUrl.searchParams.append('sa', subAction);

	baseUrl.searchParams.append(SmfVars.session.var, SmfVars.session.id);

	return baseUrl.href;
}

export const baseConfig = (params:object = {}) =>
{
	return { data: baseParams(params), headers: {
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
