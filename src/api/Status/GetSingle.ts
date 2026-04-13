import type { IFetchStatus } from "breezeTypesStatus";

import smfTextVars from "../../DataSource/Txt";
import { showError } from "../../utils/tooltip";
import { baseUrl } from "../Base";
import { resolveGet } from "../Resolvers/Get";

export const getSingleStatus = async (
	statusId: number,
): Promise<IFetchStatus | undefined> => {
	try {
		const params: Record<string, string | number> = {
			id: statusId,
		};

		const response = await fetch(baseUrl("breezeStatus", "single", [params]), {
			method: "GET",
			headers: {
				"X-SMF-AJAX": "1",
			},
		});

		return await resolveGet(response);
	} catch (_error: unknown) {
		showError(smfTextVars.error.generic);
	}
};
