import type { IFetchStatus } from "breezeTypesStatus";
import smfTextVars from "../../DataSource/Txt";
import {showError, showInfo} from "../../utils/tooltip";

export const resolveGet = async (
	response: Response,
): Promise<IFetchStatus | undefined> => {
	const { content, message } = await response.json();
  let messageToShow: string = '';

  if (!response.ok) {
    messageToShow = message ?? smfTextVars.error.generic;
	} else {
    messageToShow = message ?? '';
  }

	switch (response.status) {
		case 200:
			return content;
    case 204:
      showInfo(messageToShow)
			return;
    case 404:
    case 400:
      showError(message);
			return;
    default:
			return;
	}
};
