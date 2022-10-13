import React from 'react'
import Comment from './Comment'
import { commentType, commentList } from 'breezeTypes'
import Loading from './Loading'
import { deleteComment } from '../api/CommentApi'

export default class CommentList extends React.Component<any, any> {
  constructor (props: { comments: commentList }) {
    super(props)
    this.state = {
      list: [],
      isLoading: true
    }
  }

  updateState (newData: object): void {
    const newState = { ...this.state, ...newData }

    this.setState(newState, function () {
      console.log(newState)
    })
  }

  componentDidMount (): void {
    const tmpCommentList: any[] = []

    Object.values<commentType>(this.props.comments).forEach((comment, index) => {
      tmpCommentList[comment.id] = <Comment
        key={comment.id}
        comment={comment}
        removeComment={this.onRemoveComment}
      />
    })

    this.updateState({
      data: tmpCommentList,
      isLoading: false
    })
  }

  onRemoveComment (comment: commentType): void {
    deleteComment(comment.id).then((response) => {
      if (response.status !== 204) {
        return
      }

      this.updateState({
        list: this.state.list.filter(function (commentListItem: commentType) {
          return commentListItem.id !== comment.id
        }),
        isLoading: false
      })
    }).catch(function (error) {
      console.log(error.response.data)
      console.log(error.response.status)
      console.log(error.response.headers)
    })
  }

  render (): JSX.Element {
    return (
      this.state.isLoading === true
        ? <Loading />
        : <ul className="comments">
        {this.state.list.data}
      </ul>
    )
  }
}
