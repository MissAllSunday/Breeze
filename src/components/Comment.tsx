import React, { Component } from 'react'
import { CommentProps } from 'breezeTypes'
import Like from './Like'
import UserInfo from './user/UserInfo'

export default class Comment extends Component<CommentProps> {
  onRemove = (): void => {
    this.props.removeComment(this.props.comment)
  }

  render (): JSX.Element {
    return <div className="comment">
      <div className="floatleft">
        <UserInfo userData={this.props.comment.userData} compact={true}/>
      </div>
      <div className="floatright">
        <div className="content floatnone clear">
          {this.props.comment.body}
        </div>
        <div className="half_content smalltext">
          <Like
            item={this.props.comment.likesInfo}
          />
        </div>
        <div className="half_content smalltext">
          <span
            className="main_icons remove_button floatright pointer_cursor"
            onClick={this.onRemove}>
          delete
        </span>
        </div>

      </div>

  </div>
  }
}
