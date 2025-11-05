import type { IFetchStatus } from "breezeTypesStatus";
import { showInfo } from "../../utils/tooltip";

export const resolveGet = async (
	response: Response,
): Promise<IFetchStatus | undefined> => {
	const { content, message } = await response.json();

  if (message) {
    showInfo(message);
  }

	return content;
};
