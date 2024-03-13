import { baseUrl } from './Api';

const action = 'breezeEditor';
const subAction = 'showEditor';

export const getEditor = async (): Promise<HTMLCollection> => {
  const editorResults = await fetch(baseUrl(action, subAction), {
    method: 'POST',
  });

  return editorResults.json();
};
