import smfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseUrl } from "../Base";
import { resolveGet } from "../Resolvers/Get";
import type { IFetchStatus } from "breezeTypesStatus";

export const getStatus = async (
	type: string,
	start: number,
	cursor?: string | null,
): Promise<IFetchStatus | undefined> => {
	try {
		const params: Record<string, string | number> = {
			start: start,
			wall_id: smfVars.wall_id,
		};

		if (cursor) {
			params.cursor = cursor;
		}

		const response = await fetch(
			baseUrl("breezeStatus", type, [params]),
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
