import { StatusListProps, StatusListType, StatusType } from 'breezeTypes';
import React, { useCallback, useContext, useState } from 'react';
import toast from 'react-hot-toast';

import { deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi';
import { StatusContext, StatusDispatchContext } from '../context/statusContext';
import smfTextVars from '../DataSource/Txt';
import Editor from './Editor';
import Loading from './Loading';
import Status from './Status';

function StatusstatusListState(): React.ReactElement {
  const [isLoading, setIsLoading] = useState(false);
  const statusListState: StatusListType = useContext(StatusContext);
  const statusDispatch = useContext(StatusDispatchContext);

  if (statusListState.length === 0) {
    toast.error(smfTextVars.error.noStatus);
  }

  const createStatus = useCallback((content: string) => {
    setIsLoading(true);
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        statusDispatch({ type: 'create', statusListState: Object.values(response.content) });
        toast.success(response.message);
      }).catch((exception) => {
        toast.error(exception.toString());
      }).finally(() => {
        setIsLoading(false);
      });
  }, [statusDispatch]);

  const removeStatus = useCallback((status: StatusType) => {
    setIsLoading(true);
    deleteStatus(status.id).then((response) => {
      statusDispatch({ type: 'delete', statusListState: [status] });
      toast.success(response.message);
    }).catch((exception) => {
      toast.error(exception.toString());
    })
      .finally(() => {
        setIsLoading(false);
      });
  }, [statusDispatch]);

  return (
    <div>
      {isLoading
        ? <Loading />
        : (
          <>
            <Editor saveContent={createStatus} />
            <ul className="status">
              {statusListState.forEach((status: StatusType) => (
                <Status
                  key={status.id}
                  status={status}
                  removeStatus={removeStatus}
                />
              ))}
            </ul>
          </>
        ) }
    </div>
  );
}

export default StatusstatusListState;
