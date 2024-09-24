import smfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { baseConfig, baseUrl, resolveDelete } from '../BaseConfig';

export const deleteStatus = async (statusId: number): Promise<boolean> => {
  const deleteStatusResults = await fetch(baseUrl('breezeStatus', 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId,
    })),
  });

  return resolveDelete(deleteStatusResults, smfTextVars.general.deletedStatus);
};
