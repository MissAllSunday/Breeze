import { WallProps } from 'breezeTypes';
import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType, StatusType } from 'breezeTypesStatus';
import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { Toaster } from 'react-hot-toast';
import InfiniteScroll from 'react-infinite-scroll-component';

import {
  deleteStatus,
  getStatus, postStatus, ServerGetStatusResponse, ServerPostStatusResponse,
} from './api/StatusApi';
import Editor from './components/Editor';
import Loading from './components/Loading';
import Status from './components/Status';
import { PermissionsContext } from './context/PermissionsContext';
import PermissionsDefault from './DataSource/Permissions';
import smfVars from './DataSource/SMF';
import smfTextVars from './DataSource/Txt';
import { showError, showInfo } from './utils/tooltip';

export default function Wall(props: WallProps): React.JSX.Element {
  const [statusList, setStatusList] = useState<StatusListType>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [permissions, setPermissions] = useState<PermissionsContextType>(PermissionsDefault);
  const [paginationTotal, setPaginationTotal] = useState<number>(0);

  useEffect(() => {
    getStatus(props.wallType, 0)
      .then((statusListResponse: ServerGetStatusResponse) => {
        const fetchedStatusList: StatusListType = Object.values(statusListResponse.content.data);
        setStatusList(fetchedStatusList);
        setPermissions(statusListResponse.content.permissions);
        setPaginationTotal(statusListResponse.content.total);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [props.wallType]);

  const fetchNextStatus = useCallback(() => {
    if (statusList.length >= paginationTotal) {
      showInfo(smfTextVars.general.end);
      return;
    }

    setIsLoading(true);

    getStatus(props.wallType, statusList.length)
      .then((statusListResponse: ServerGetStatusResponse) => {
        setStatusList(statusList.concat(Object.values(statusListResponse.content.data)));
      }).finally(() => {
        setIsLoading(false);
      });
  }, [props, statusList, paginationTotal]);

  const createStatus = useCallback((content: string) => {
    setIsLoading(true);

    postStatus(content).then((response: ServerPostStatusResponse) => {
      const newStatus:StatusListType = Object.values(response.content);

      for (const key in newStatus) {
        setStatusList([...statusList, newStatus[key]]);
      }

      showInfo(response.message);
    }).catch((exception) => {
      showError(exception.toString());
    }).finally(() => {
      setIsLoading(false);
    });
  }, [statusList]);

  const removeStatus = useCallback((currentStatus: StatusType) => {
    setIsLoading(true);

    deleteStatus(currentStatus.id).then((response) => {
      setStatusList(statusList.filter((status: StatusType) => currentStatus.id !== status.id));
      showInfo(response.message);
    }).finally(() => {
      setIsLoading(false);
    });
  }, [statusList]);

  return (
      <>
        <Editor saveContent={createStatus} isFull={true}/>
        <Toaster toastOptions={{
          duration: 4000,
        }}/>
        {statusList.length === 0 && !isLoading ? showInfo(smfTextVars.error.noStatus) : ''}
        <InfiniteScroll
          dataLength={statusList.length}
          next={fetchNextStatus}
          hasMore={true}
          loader={null}
          endMessage={null}
        >
          <PermissionsContext.Provider value={permissions}>
            <ul className="status">
              {isLoading ? <Loading/> : statusList.map((singleStatus: StatusType) => (
                <Status
                  key={singleStatus.id}
                  status={singleStatus}
                  removeStatus={removeStatus}
                />
              ))}
            </ul>
          </PermissionsContext.Provider>
        </InfiniteScroll>
      </>
  );
}
