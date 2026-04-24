import { showError, showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";

export const resolveDelete = async (
	response: Response,
	successMessage: string,
): Promise<boolean> => {
	const deleted: boolean = response.ok && response.status === 204;

	let message = "";
	let token = null;

	// The backend may include a new CSRF token on success, but strict
	// 204 No Content responses can have an empty body.
	try {
		const body = await response.json();
		message = body.message ?? "";
		token = body.token ?? null;
	} catch {
		// Empty or unparseable body.
	}

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
