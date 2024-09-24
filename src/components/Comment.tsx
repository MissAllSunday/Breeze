import { CommentProps } from 'breezeTypesComments';
import React, { useCallback, useContext, useState } from 'react';

import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { Like } from './Like';
import Avatar from './user/Avatar';

function Comment(props: CommentProps): React.ReactElement {
  const [classType, setClassType] = useState(props.comment.isNew ? 'fadeIn' : '');
  const timeStamp = props.comment.createdAt;
  const permissions = useContext(PermissionsContext);

  const removeComment = useCallback(() => {
    if (!window.confirm(smfVars.youSure) || !permissions.Comments.delete) {
      return;
    }
    props.removeComment(props.comment);
  }, [props, permissions]);

  return (
    <div className={`${classType} comment`}>
      <div className="avatar_compact">
        <Avatar
          href={props.comment.userData.avatar.url}
          userName={props.comment.userData.username}
        />
        <span dangerouslySetInnerHTML={{ __html: props.comment.userData.link_color }}/>
      </div>
      <div className="comment_compact content">
        {props.comment.body}
      </div>
      <div className="half_content">
        <Like
          item={props.comment.likesInfo}
        />
      </div>
      <div className="half_content">
        <div className={'info_bar'}>
          <span dangerouslySetInnerHTML={{ __html: timeStamp }} className={'time_stamp'}/>
          {permissions.Comments.delete && <span
            className="main_icons remove_button floatright pointer_cursor"
            title={smfTextVars.general.delete}
            onClick={removeComment}
          >
            {smfTextVars.general.delete}
          </span>}
        </div>
      </div>
      <hr/>
    </div>
  );
}

export default React.memo(Comment);
