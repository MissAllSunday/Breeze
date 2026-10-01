import smfTextVars from "../../DataSource/Txt";
import { baseConfig, baseUrl } from "../Base";
import { resolveDelete } from "../Resolvers/Delete";

export const deleteStatus = async (statusId: number): Promise<boolean> => {
	const deleteStatusResults = await fetch(
		baseUrl("breezeStatus", "deleteStatus"),
		{
			method: "POST",
			body: JSON.stringify(
				baseConfig({
					id: statusId,
				}),
			),
		},
	);

	return resolveDelete(deleteStatusResults, smfTextVars.general.deletedStatus);
};
