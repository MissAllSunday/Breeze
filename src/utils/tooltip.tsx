import React from 'react';
import toast from 'react-hot-toast';

export interface ServerResponse {
  content: object
  message: string
}

export const showError = (response: Response): void => {
  response.json().then((serverResponse: ServerResponse) => {
    toast.custom(<div className={'errorbox'}>
    {serverResponse.message}
    </div>);
  });
};

export const showInfo = (message: string): void => {
  toast.custom(<div className={'infobox'}>
    {message}
  </div>);
};
