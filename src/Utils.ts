const canUseLocalStorage = (): boolean => {
  const storage = window.localStorage;

  try {
    const x = 'breeze_storage_test';
    storage.setItem(x, x);
    storage.removeItem(x);

    return true;
  } catch (e) {
    return e instanceof DOMException && (
      e.name === 'QuotaExceededError'
        || e.name === 'NS_ERROR_DOM_QUOTA_REACHED')
        && storage.length !== 0;
  }
};

const setLocalObject = (keyName: string, objectToStore: object): boolean => {
  if (!canUseLocalStorage()) {
    return false;
  }

  localStorage.setItem(keyName, JSON.stringify(objectToStore));

  return true;
};

const getLocalObject = (keyName: string): boolean => {
  if (!canUseLocalStorage()) {
    return false;
  }

  const objectStored = JSON.parse(localStorage.getItem(keyName) as string);

  if (objectStored !== null) {
    return objectStored;
  }
  return false;
};

const decode = (html: string): string | null => {
  const decoder = document.createElement('div');
  decoder.innerHTML = html;
  return decoder.textContent;
};

const utils = {
  decode,
  getLocalObject,
  setLocalObject,
  canUseLocalStorage,
};

export default utils;
