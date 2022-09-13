import axios, {AxiosResponse} from 'axios';
import SmfVars from '../DataSource/SMF';

export const baseUrl = (action: string, subAction:string) =>
{
	let baseUrl = new URL(SmfVars.scriptUrl);

	baseUrl.searchParams.append('action', action);
	baseUrl.searchParams.append('sa', subAction);

	baseUrl.searchParams.append(SmfVars.session.var, SmfVars.session.id);

	return baseUrl.href;
}

export const baseParams = (params: object = {}) =>
{
	return {
		wallId: SmfVars.wallId,
		...params
	};
}
