import { baseConfig, baseUrl } from './Api';

const action = 'breeze';

export const getEditor = async (): Promise<HTMLCollection> => {
  const editorResults = await fetch(baseUrl(action, 'editor'), {
    method: 'POST',
  });

  return editorResults.json();
};
