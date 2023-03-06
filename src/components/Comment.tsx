import React, { useCallback, useState } from 'react'
import { CommentProps } from 'breezeTypes'
import Like from './Like'
import Avatar from './user/Avatar'
import smfVars from '../DataSource/SMF'

function Comment (props: CommentProps): React.ReactElement {
  const [classType, setClassType] = useState(props.comment.isNew ? 'fadeIn' : '')
  const timeStamp = new Date(props.comment.createdAt)

  const removeComment = useCallback(() => {
    if (!confirm(smfVars.youSure)) {
      return
    }

    setClassType('fadeOut')
    props.removeComment(props.comment)
  }, [props])

  return (<div className={classType + ' comment'}>
    <div className="avatar_compact">
      <Avatar
        href={props.comment.userData.avatar.url}
        userName={props.comment.userData.username}/>
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
          <span
            className="main_icons remove_button floatright pointer_cursor"
            onClick={removeComment}>
          delete
        </span>
      </div>
    </div>
    <hr/>
  </div>)
}

export default React.memo(Comment)
