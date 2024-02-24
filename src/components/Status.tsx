import { StatusProps } from 'breezeTypes';
import * as React from 'react';
import { useCallback, useState } from 'react';

import smfVars from '../DataSource/SMF';
import CommentList from './CommentList';
import Like from './Like';
import UserInfo from './user/UserInfo';

function Status(props: StatusProps): React.ReactElement {
  const [classType, setClassType] = useState(props.status.isNew ? 'fadeIn' : '');
  const timeStamp = new Date(props.status.createdAt);

  const ref = React.useRef<null | HTMLDivElement>(null);

  React.useLayoutEffect(() => {
    const node = ref.current;

    if (node && props.status.isNew) {
      node.scrollIntoView({ behavior: 'smooth' });
    }
  });

  const removeStatus = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }

    setClassType('fadeOut');
    props.removeStatus(props.status);
  }, [props]);

  return (
    <li
      className={`${classType} status`}
      key={props.status.id}
      id={`status-${props.status.id.toString()}`}
      ref={ref as React.LegacyRef<HTMLLIElement>}
    >
      <div className="floatleft userinfo">
        <UserInfo userData={props.status.userData} />
      </div>
      <div className="windowbg floatright">
        <div className="content" title={timeStamp.toLocaleString()}>
          {props.status.body}
        </div>
        <div className="half_content">
          <Like
            item={props.status.likesInfo}
          />
        </div>
        <div className="half_content">
          <span
            className="main_icons remove_button floatright pointer_cursor"
            onClick={removeStatus}
          >
            delete
          </span>
        </div>
        <hr />
        <CommentList
          CommentList={props.status.comments}
          statusId={props.status.id}
        />
      </div>
    </li>
  );
}

export default Status;
