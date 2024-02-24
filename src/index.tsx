import { StatusListType, StatusType } from 'breezeTypes';
import React from 'react';
import { createRoot } from 'react-dom/client';
import toast from 'react-hot-toast';

import { getStatus, ServerGetStatusResponse } from './api/StatusApi';
import Loading from './components/Loading';
import Wall from './Wall';

const rootElement = (document.getElementById('root') ?? document.createElement('div'));
const root = createRoot(rootElement);
const wallType = rootElement.getAttribute('wallType') ?? 'profile';
// @ts-expect-error Backend variable or default value
const pagination: number = window.breezePagination ?? 5;

getStatus(wallType)
  .then((statusListResponse: ServerGetStatusResponse) => {
    const fetchedStatusList: StatusListType = Object.values(statusListResponse.content.data);
    root.render(
      <React.StrictMode>
        <Wall statusList={fetchedStatusList} />
      </React.StrictMode>,
    );
  })
  .catch((exception) => {
    toast.error(exception.toString());
  })
  .finally(() => {
  });

