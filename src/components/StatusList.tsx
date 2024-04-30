import { StatusListType, StatusType } from 'breezeTypesStatus';
import React, { useCallback } from 'react';
import toast from 'react-hot-toast';

import { deleteStatus, getStatus, postStatus, ServerGetStatusResponse, ServerPostStatusResponse } from '../api/StatusApi';
import smfTextVars from '../DataSource/Txt';
import Editor from './Editor';

function StatusList(statusList: StatusListType): React.ReactElement {

  if (statusList.statusList.length === 0) {
    toast.error(smfTextVars.error.noStatus);
  }

  const createStatus = useCallback((content: string) => {
    // setIsLoading(true);
    postStatus(content)
      .then((response: ServerPostStatusResponse) => {
        const newStatus = Object.values(response.content);
        for (const key in newStatus) {
          // setStatus([...statusListState, newStatus[key]]);
        }
        toast.success(response.message);
      }).catch((exception) => {
        toast.error(exception.toString());
      }).finally(() => {
        // setIsLoading(false);
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
      <Editor saveContent={createStatus} />
      <ul className="status">
        {/*{statusListState.length === 0 ? <Loading /> : statusListState.forEach((status: StatusType) => (*/}
        {/*  <Status*/}
        {/*    key={status.id}*/}
        {/*    status={status}*/}
        {/*    removeStatus={removeStatus}*/}
        {/*  />*/}
        {/*))}*/}
      </ul>
    </div>
  );
}

export default StatusList;
