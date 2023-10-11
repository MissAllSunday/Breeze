import { StatusListType, WallProps } from 'breezeTypes';
import React, { useEffect, useReducer, useState } from 'react';
import toast, { Toaster } from 'react-hot-toast';

import {
  getStatus, ServerGetStatusResponse,
} from './api/StatusApi';
import Loading from './components/Loading';
import StatusList from './components/StatusList';
import { StatusProvider, statusReducer } from './context/statusContext';

export default function Wall(props: WallProps): React.ReactElement {
  const [statusListState, dispatch] = useReducer(statusReducer, []);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setIsLoading(true);
    getStatus(props.wallType)
      .then((statusListResponse: ServerGetStatusResponse) => {
        const fetchedStatusList: StatusListType = Object.values(statusListResponse.content.data);
        dispatch({ type: 'create', statusListState: fetchedStatusList });
      })
      .catch((exception) => {
        toast.error(exception.toString());
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [props.wallType]);

  return (
    <div>
      <Toaster />
      {isLoading
        ? <Loading />
        : <StatusProvider />}
    </div>
  );
}
