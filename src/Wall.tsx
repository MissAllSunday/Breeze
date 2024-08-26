import { WallProps } from 'breezeTypes';
import { PermissionsContextType } from 'breezeTypesPermissions';
import { StatusListType, StatusType } from 'breezeTypesStatus';
import React, { useCallback, useEffect, useState } from 'react';
import { Toaster } from 'react-hot-toast';

import { IServerFetchResponse } from './api/Api';
import {
  deleteStatus,
  getStatus, postStatus,
} from './api/StatusApi';
import Editor from './components/Editor';
import Loading from './components/Loading';
import Status from './components/Status';
import { PermissionsContext } from './context/PermissionsContext';
import PermissionsDefault from './DataSource/Permissions';
import smfTextVars from './DataSource/Txt';
import { showError, showInfo } from './utils/tooltip';

export default function Wall(props: WallProps): React.JSX.Element {
  const [statusList, setStatusList] = useState<StatusListType>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [permissions, setPermissions] = useState<PermissionsContextType>(PermissionsDefault);
  const [paginationTotal, setPaginationTotal] = useState<number>(0);
  const [loadedMore, setLoadedMore] = useState(0);
  const ref = React.useRef<null | HTMLInputElement>(null);

  useEffect(()=> ref.current?.scrollIntoView({ behavior: 'smooth', block:'end' }), [loadedMore]);

  useEffect(() => {
    getStatus(props.wallType, 0)
      .then((statusListResponse: IServerFetchResponse) => {
        if (typeof statusListResponse === 'undefined') {
          return;
        }

        const fetchedStatusList: StatusListType = Object.values(statusListResponse.data);
        setStatusList(fetchedStatusList);
        setPermissions(statusListResponse.permissions);
        setPaginationTotal(statusListResponse.total);
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
      .then((statusListResponse: IServerFetchResponse) => {
        setStatusList(statusList.concat(Object.values(statusListResponse.data)));
      }).finally(() => {
        setIsLoading(false);
        setLoadedMore(statusList.length);
      });
  }, [props, statusList, paginationTotal]);

  const createStatus = useCallback((content: string) => {
    setIsLoading(true);

    postStatus(content).then((newStatus: StatusListType) => {
      for (const key in newStatus) {
        setStatusList([...statusList, newStatus[key]]);
      }
    }).finally(() => {
      setIsLoading(false);
    });

    return true;
  }, [statusList]);

  const removeStatus = useCallback((currentStatus: StatusType) => {
    setIsLoading(true);

    deleteStatus(currentStatus.id).then((deleted: boolean) => {

      if (deleted) {
        setStatusList(statusList.filter((status: StatusType) => currentStatus.id !== status.id));
      }
    }).finally(() => {
      setIsLoading(false);
    });
  }, [statusList]);

  const goUp = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
      <>
        <Editor saveContent={createStatus} isFull={true}/>
        <Toaster toastOptions={{
          duration: 4000,
        }}/>
        <PermissionsContext.Provider value={permissions}>
          <ul className="status">
            {statusList.map((singleStatus: StatusType) => (
              <Status
                key={singleStatus.id}
                status={singleStatus}
                removeStatus={removeStatus}
              />
            ))}
          </ul>
          <div id="post_confirm_buttons">
            {statusList.length < paginationTotal ? <input type="submit"
                    value={smfTextVars.general.loadMore}
                    name={smfTextVars.general.loadMore}
                    className="button"
                    onClick={fetchNextStatus}
                    ref={ref as React.LegacyRef<HTMLInputElement>}/> : ''}
            <input type="submit"
               value={smfTextVars.general.goUp}
               name={smfTextVars.general.goUp}
               className="button"
               onClick={goUp} />
          </div>
        </PermissionsContext.Provider>
        {isLoading ? <Loading/> : ''}
      </>
  );
}
