import { showError, showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";

export const resolveDelete = async (
	response: Response,
	successMessage: string,
): Promise<boolean> => {
	const { message, token } = await response.json();
	const deleted: boolean = response.ok && response.status === 204;

	if (token) {
		updateCsrfToken(token);
	}

	if (!deleted) {
		showError(message);
	} else {
		showInfo(successMessage);
	}

	return deleted;
};
