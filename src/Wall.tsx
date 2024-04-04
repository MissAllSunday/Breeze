import { StatusListType, StatusType, WallProps } from 'breezeTypes';
import React, { useCallback, useEffect, useState } from 'react';
import toast, { Toaster } from 'react-hot-toast';

import {
  deleteStatus,
  getStatus, postStatus, ServerGetStatusResponse, ServerPostStatusResponse,
} from './api/StatusApi';
import Loading from './components/Loading';
import Status from './components/Status';
import smfTextVars from './DataSource/Txt';

export default function Wall(props: WallProps): React.ReactElement {
  const [statusList, setStatusList] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    getStatus(props.wallType)
      .then((statusListResponse: ServerGetStatusResponse) => {
        const fetchedStatusList: StatusListType = Object.values(statusListResponse.content.data);
        setStatusList(fetchedStatusList);
      })
      .catch(exception => {})
      .finally(() => {
        setIsLoading(false);
      });
  }, [props.wallType]);

  const createStatus = useCallback((content: string) => {
    setIsLoading(true);
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        const newStatus = Object.values(response.content);
        for (const key in newStatus) {
          // setStatusList([...statusList, newStatus[key]]);
        }
        toast.success(response.message);
      }).catch((exception) => {
        toast.error(exception.toString());
      }).finally(() => {
        setIsLoading(false);
      });
  }, []);

  const removeStatus = useCallback((currentStatus: StatusType) => {
    // setIsLoading(true);
    deleteStatus(currentStatus.id).then((response) => {
      // setStatus(statusListState.filter((status: StatusType) => statusListState.includes(status) === false));
      toast.success(response.message);
    }).catch((exception) => {
      toast.error(exception.toString());
    })
      .finally(() => {
        // setIsLoading(false);
      });
  }, []);

  return (
    <div>
        {statusList.length === 0 && !isLoading ? <div className={'noticebox'}> {smfTextVars.error.noStatus} </div> : ''}
      <ul className="status">
        {isLoading ? <Loading /> : statusList.map((singleStatus: StatusType) => (
            <Status
                key={singleStatus.id}
                status={singleStatus}
                removeStatus={removeStatus}
            />
        ))}
      </ul>
    </div>
  );
}
