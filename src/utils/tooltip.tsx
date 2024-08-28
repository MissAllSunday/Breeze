import React, { ReactElement } from 'react';
import toast from 'react-hot-toast';

export interface ServerResponse {
  content: object
  message: string
}

export const showInfo = (message: string): void => {
  toast.custom(displayMessage(message));
};

export const showError = (message: string): void => {
  toast.custom(displayMessage(message, 'error'));
};

export const displayMessage = (message: string, type = 'info'): ReactElement => {
  return (<div className={type + 'box'}>
    {message}
  </div>);
};
