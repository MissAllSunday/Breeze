import { CommentProps } from 'breezeTypes';
import React, { useCallback, useContext, useState } from 'react';

import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import Like from './Like';
import Avatar from './user/Avatar';


function Comment(props: CommentProps): React.ReactElement {
  const [classType, setClassType] = useState(props.comment.isNew ? 'fadeIn' : '');
  const timeStamp = new Date(props.comment.createdAt);
  const permissions = useContext(PermissionsContext);

  const removeComment = useCallback(() => {
    if (!window.confirm(smfVars.youSure) || !permissions.Comments.delete) {
      return;
    }

    setClassType('fadeOut');
    props.removeComment(props.comment);
  }, [props, permissions]);

  return (
    <div className={`${classType} comment`}>
      <div className="avatar_compact">
        <Avatar
          href={props.comment.userData.avatar.url}
          userName={props.comment.userData.username}
        />
        <div dangerouslySetInnerHTML={{ __html: props.comment.userData.link_color }} className="link_compact" />
        <span className="floatright smalltext">{timeStamp.toLocaleString()}</span>
      </div>
      <div className="floatnone clear">
        <div className="content">
          {props.comment.body}
        </div>
        <div className="half_content ">
          <Like
            item={props.comment.likesInfo}
          />
        </div>
        <div className="half_content">
          { permissions.Comments.delete && <span
            className="main_icons remove_button floatright pointer_cursor"
            title={smfTextVars.general.delete}
            onClick={removeComment}
          >
            {smfTextVars.general.delete}
          </span>}
        </div>
      </div>
      <hr />
    </div>
  );
}

export default React.memo(Comment);
