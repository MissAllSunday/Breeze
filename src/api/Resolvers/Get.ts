import type { IFetchStatus } from "breezeTypesStatus";
import { showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";

export const resolveGet = async (
	response: Response,
): Promise<IFetchStatus | undefined> => {
	const { content, message, token } = await response.json();

	if (token) {
		updateCsrfToken(token);
	}

	if (message) {
		showInfo(message);
	}

	return content;
};
