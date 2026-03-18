import { showError, showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";

export const resolveDelete = async (
	response: Response,
	successMessage: string,
): Promise<boolean> => {
	const deleted: boolean = response.ok && response.status === 204;

	if (!deleted) {
		const { message, token } = await response.json();

		if (token) {
			updateCsrfToken(token);
		}

		showError(message);
	} else {
		showInfo(successMessage);
	}

	return deleted;
};
