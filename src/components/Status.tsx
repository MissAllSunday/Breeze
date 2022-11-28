import * as React from 'react'
import { StatusProps, commentType } from 'breezeTypes'
import Like from './Like'
import { CommentList } from './CommentList'
import Editor from './Editor'
import { postComment } from '../api/CommentApi'

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
    return <li key={this.props.status.id}>
  <div className='breeze_avatar avatar_status floatleft'>
    <div className='windowbg'>
      <h4 className='floatleft'>
        h4 heading
      </h4>
      <div className='floatright smalltext'>
        {this.props.status.createdAt}
        <span
          className="main_icons remove_button floatright pointer_cursor"
          onClick={this.removeStatus}>
          delete
        </span>
      </div>
      <br />
        <div className='content'>
          {this.props.status.body}
          <Like
            item={this.props.status.likesInfo}
          />
        </div>
        <CommentList
          commentList={this.props.status.comments}
          removeComment={this.removeComment}
        />
      <div className='comment_posting'>
        <Editor saveContent={this.newcomment} />
      </div>
    </div>
  </div>
  <hr />
</li>
  }
}
