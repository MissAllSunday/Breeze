import * as React from 'react'
import { StatusProps, commentType } from 'breezeTypes'
import Like from './Like'
import { CommentList } from './CommentList'
import Editor from './Editor'

export default class Status extends React.Component<StatusProps> {
  constructor (props: any) {
    super(props)
    this.state = {
      isLoading: true
    }
  }

  onRemove = (): void => {
    this.props.removeStatus(this.props.status)
  }

  onRemoveComment = (comment: commentType): void => {

  }

  onNewComment = (content: string): void => {

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
        &nbsp;<span className="main_icons remove_button floatright pointer_cursor" onClick={this.onRemove}>delete</span>
      </div>
      <br />
        <div className='content'>
          <hr />
          {this.props.status.body}
          <Like
            item={this.props.status.likesInfo}
          />
        </div>
        <CommentList
          commentList={this.props.status.comments}
          removeComment={this.onRemoveComment}
        />
      <div className='comment_posting'>
        <Editor saveContent={this.onNewComment} />
      </div>
    </div>
  </div>
</li>
  }
}
