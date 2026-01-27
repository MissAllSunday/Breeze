import smfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import { baseConfig } from "../Base";
import { baseUrl } from "../Base";
import { resolveDelete } from "../Resolvers/Delete";

export const deleteStatus = async (statusId: number): Promise<boolean> => {
	const deleteStatusResults = await fetch(
		baseUrl("breezeStatus", "deleteStatus"),
		{
			method: "POST",
			body: JSON.stringify(
				baseConfig({
					id: statusId,
					user_id: smfVars.user_id,
				}),
			),
		},
	);

	return resolveDelete(deleteStatusResults, smfTextVars.general.deletedStatus);
};
