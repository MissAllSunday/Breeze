import smfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseUrl } from "../Base";
import { resolveGet } from "../Resolvers/Get";
import type { IFetchStatus } from "breezeTypesStatus";

export const getStatus = async (
	type: string,
	start: number,
): Promise<IFetchStatus | undefined> => {
	try {
		const response = await fetch(
			baseUrl("breezeStatus", type, [{ start: start, wall_id: smfVars.wall_id }]),
			{
				method: "GET",
				headers: {
					"X-SMF-AJAX": "1",
				},
			},
		);

		return await resolveGet(response);
	} catch (_error: unknown) {
		showError(smfTextVars.error.generic);
	}
};
