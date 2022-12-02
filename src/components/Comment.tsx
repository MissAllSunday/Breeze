import React, { Component } from 'react'
import { CommentProps } from 'breezeTypes'
import Like from './Like'

export default class Comment extends Component<CommentProps> {
  onRemove = (): void => {
    this.props.removeComment(this.props.comment)
  }

  render (): JSX.Element {
    return <div className="comment">
      <div className="avatar">
        <img src={this.props.comment.userData.avatar.href} alt={this.props.comment.userData.username}/>
      </div>
      <div onClick={ this.onRemove }>remove comment</div>
      {this.props.comment.body}
      <Like
        item={this.props.comment.likesInfo}
      />
  </div>
  }
}
