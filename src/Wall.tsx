import { StatusListType, StatusType, WallProps } from 'breezeTypes';
import React, { useCallback, useEffect, useMemo, useState } from 'react';
import toast, { Toaster } from 'react-hot-toast';

import {
  deleteStatus,
  getStatus, postStatus, ServerGetStatusResponse, ServerPostStatusResponse,
} from './api/StatusApi';
import Loading from './components/Loading';
import { SMFEditorEvent } from './components/SMFEditorEvent';
import Status from './components/Status';
import ToolTip from './components/ToolTip';
import smfVars from './DataSource/SMF';
import smfTextVars from './DataSource/Txt';

export default function Wall(props: WallProps): React.ReactElement {
  const [statusList, setStatusList] = useState<StatusListType>([]);
  const [isLoading, setIsLoading] = useState(true);
  const editorElement = useMemo(() => { return document.getElementById(smfVars.editorId) || new HTMLElement();}, []);
  const editorRef = SMFEditorEvent(() => {

    const editorContent: string = props.smfEditor.instance(editorElement).val();

    createStatus(editorContent);
  });

  useEffect(() => {
    getStatus(props.wallType)
      .then((statusListResponse: ServerGetStatusResponse) => {
        const fetchedStatusList: StatusListType = Object.values(statusListResponse.content.data);
        setStatusList(fetchedStatusList);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [props.wallType]);

  const createStatus = useCallback((content: string) => {
    setIsLoading(true);

    editorElement.setAttribute('disabled', '');
    toast.promise(postStatus(content), {
      loading: <Loading />,
      success: (data) => data.message,
      error: (err) => err,
    }).then((response: ServerPostStatusResponse) => {
      const newStatus:StatusListType = Object.values(response.content);

      for (const key in newStatus) {
        setStatusList([...statusList, newStatus[key]]);
      }
      props.smfEditor.instance(editorElement).val('');
    }).finally(() => {
      setIsLoading(false);
      editorElement.removeAttribute('disabled');
    });
  }, [statusList, editorElement, props.smfEditor]);

  const removeStatus = useCallback((currentStatus: StatusType) => {
    setIsLoading(true);
    toast.promise(deleteStatus(currentStatus.id), {
      loading: <Loading />,
      success: (data) => data.message,
      error: (err) => err,
    }).then((response) => {
      setStatusList(statusList.filter((status: StatusType) => currentStatus.id !== status.id));
    }).finally(() => {
      setIsLoading(false);
      toast.dismiss();
    });
  }, [statusList]);

  return (
    <div ref={editorRef}>
      <Toaster />
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
