import { showError, showInfo } from '../../utils/tooltip';

export const resolveDelete = async (response: Response, successMessage: string):Promise<boolean> => {

  const deleted: boolean = response.ok && response.status === 204;

  if (!deleted) {
    const { message } = await response.json();
    showError(message);
  } else {
    showInfo(successMessage);
  }

  return deleted;
};
