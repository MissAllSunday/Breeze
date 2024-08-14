import { CommentListType, CommentType } from 'breezeTypesComments';
import { StatusProps } from 'breezeTypesStatus';
import * as React from 'react';
import { useCallback, useContext, useState } from 'react';

import { deleteComment, postComment, ServerCommentData } from '../api/CommentApi';
import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError, showInfo } from '../utils/tooltip';
import Comment from './Comment';
import Editor from './Editor';
import { Like } from './Like';
import Loading from './Loading';
import UserInfo from './user/UserInfo';

function Status(props: StatusProps): React.ReactElement {
  const [classType, setClassType] = useState(props.status.isNew ? 'fadeIn' : '');
  const timeStamp = new Date(props.status.createdAt);
  const [commentsList, setCommentsList] = useState<CommentListType>(Object.values(props.status.comments));
  const [isLoading, setIsLoading] = useState(false);
  const permissions = useContext(PermissionsContext);

  const ref = React.useRef<null | HTMLDivElement>(null);

  React.useLayoutEffect(() => {
    const node = ref.current;

    if (node && props.status.isNew) {
      node.scrollIntoView({ behavior: 'smooth' });
    }
  });

  const removeStatus = useCallback(() => {
    if (!window.confirm(smfVars.youSure) || !permissions.Status.delete) {
      return;
    }

    props.removeStatus(props.status);
  }, [permissions.Status.delete, props]);

  const createComment = useCallback((content: string) => {
    if (!permissions.Comments.post) {
      return;
    }

    setIsLoading(true);

    postComment({
      statusId: props.status.id,
      body: content,
    }).then((response: ServerCommentData) => {
      const newComments:CommentListType = Object.values(response.content);

      for (const key in newComments) {
        setCommentsList([...commentsList, newComments[key]]);
      }
      showInfo(response.message);

      return true;
    }).finally(() => {
      setIsLoading(false);
    });
  }, [props.status.id, commentsList, permissions.Comments.post]);

  const removeComment = useCallback((comment: CommentType) => {
    setIsLoading(true);
    deleteComment(comment.id).then((response) => {
      if (response) {
        setCommentsList(commentsList.filter((currentComment: CommentType) => currentComment.id !== comment.id));
      }
    }).finally(() => {
      setIsLoading(false);
    });
  }, [permissions.Comments.delete]);

  const showEditor = permissions.Comments.post ? <Editor saveContent={createComment}/> : '';

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
        <div className="content" title={timeStamp.toLocaleString()} dangerouslySetInnerHTML={{ __html: props.status.body }} />
        <div className="half_content">
          <Like
            item={props.status.likesInfo}
          />
        </div>
        <div className="half_content">
          {permissions.Status.delete &&
            <span
              className="main_icons remove_button floatright pointer_cursor"
              title={smfTextVars.general.delete}
              onClick={removeStatus}
            >
            {smfTextVars.general.delete}
          </span>
          }
        </div>
        <hr/>
        <div className="comment_posting">
          {isLoading
            ? <Loading/>
            : showEditor}
        </div>
        <ul className="status">
        {commentsList.map((comment: CommentType) => (
            <Comment
              key={comment.id}
              comment={comment}
              removeComment={removeComment}
            />
        ))}
        </ul>
      </div>
    </li>
  );
}

export default Status;
