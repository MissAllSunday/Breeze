import React, { Component } from 'react'
import { CommentProps, CommentState } from 'breezeTypes'
import Like from './Like'
import Avatar from './user/Avatar'

class Comment extends Component<CommentProps, CommentState> {
  public readonly state: Readonly<CommentState> = {
    visible: true,
    classType: this.props.comment.isNew ? 'fadeIn' : ''
  }

  onRemove = (): void => {
    this.props.removeComment(this.props.comment)
    this.setState({
      visible: false,
      classType: 'fadeOut'
    })
  }

  render (): JSX.Element {
    const timeStamp = new Date(this.props.comment.createdAt)

    return <div className={(this.state.visible ? 'fadeIn' : 'fadeOut') + ' comment'}>
      <div className="avatar_compact">
        <Avatar
          href={this.props.comment.userData.avatar.url}
          userName={this.props.comment.userData.username}/>
        <div dangerouslySetInnerHTML={{ __html: this.props.comment.userData.link_color }} className="link_compact" />
        <span className="floatright smalltext">{timeStamp.toLocaleString()}</span>
      </div>
      <div className="floatnone clear">
        <div className="content">
          {this.props.comment.body}
        </div>
        <div className="half_content ">
          <Like
            item={this.props.comment.likesInfo}
          />
        </div>
        <div className="half_content">
          <span
            className="main_icons remove_button floatright pointer_cursor"
            onClick={this.onRemove}>
          delete
        </span>
        </div>

      </div>
      <hr/>

  </div>
  }
}

export default React.memo(Comment)
