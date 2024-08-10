import React, { ReactElement } from 'react';
import toast from 'react-hot-toast';

export interface ServerResponse {
  content: object
  message: string
}

export const showError = (response: Response): void => {
  response.json().then((serverResponse: ServerResponse) => {
    toast.custom(displayMessage(serverResponse.message, 'error'));
  });
};

export const showInfo = (message: string): void => {
  toast.custom(displayMessage(message));
};

export const showErrorMessage = (message: string): void => {
  toast.custom(displayMessage(message, 'error'));
};

export const displayMessage = (message: string, type = 'info'): ReactElement => {
  return (<div className={type + 'box'}>
    {message}
  </div>);
};
