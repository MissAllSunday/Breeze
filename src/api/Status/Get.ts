import smfVars from '../../DataSource/SMF';
import smfTextVars from '../../DataSource/Txt';
import { showError } from '../../utils/tooltip';
import { baseUrl } from '../Base';
import { resolveGet } from '../Resolvers/Get';

export const getStatus = async (type: string, start: number): Promise<any | void> => {
  try {
    const response =  await fetch(baseUrl('breezeStatus', type,
      [ { start: start, wallId: smfVars.wallId } ]), {
      method: 'GET',
      headers: {
        'X-SMF-AJAX': '1',
      },
    });

    return await resolveGet(response);
  } catch (error:unknown) {
    showError(smfTextVars.error.generic);
  }
};
