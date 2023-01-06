import * as React from 'react'
import { StatusProps, commentType } from 'breezeTypes'
import Like from './Like'
import { CommentList } from './CommentList'
import Editor from './Editor'
import { postComment } from '../api/CommentApi'
import UserInfo from './user/UserInfo'

export default class Status extends React.Component<StatusProps> {
  constructor (props: any) {
    super(props)
    this.state = {
      isLoading: true
    }
  }

  removeStatus = (): void => {
    this.props.removeStatus(this.props.status)
  }

  newcomment = (content: string): void => {
    postComment({
      statusID: this.props.status.id,
      body: content
    }).then((response) => {
      this.props.createComment(Object.values(response.data.content), this.props.status.id)
    }).catch(() => {

    })
  }

  removeComment = (comment: commentType): void => {
    this.props.removeComment(this.props.status, comment)
  }

  render (): JSX.Element {
    const timeStamp = new Date(this.props.status.createdAt)

    return <li className="status" key={this.props.status.id}>
    <div className="floatleft">
      <UserInfo userData={this.props.status.userData}/>
    </div>

    <div className='windowbg floatright'>
      <div className='content' title={timeStamp.toLocaleString()}>
        {this.props.status.body}
      </div>
      <div className='half_content'>
        <Like
          item={this.props.status.likesInfo}
        />
      </div>
      <div className='half_content'>
        <span
          className="main_icons remove_button floatright pointer_cursor"
          onClick={this.removeStatus}>
          delete
        </span>
      </div>
      <hr />
      <CommentList
        commentList={this.props.status.comments}
        removeComment={this.removeComment}
      />
      <div className='comment_posting'>
        <Editor saveContent={this.newcomment} />
      </div>
    </div>
</li>
  }
}
