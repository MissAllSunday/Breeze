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
        <UserInfo userData={this.props.comment.userData}/>
      </div>
      <div className="floatright">
        <div onClick={ this.onRemove }>remove comment</div>
        {this.props.comment.body}
        <Like
          item={this.props.comment.likesInfo}
        />
      </div>

  </div>
  }
}
