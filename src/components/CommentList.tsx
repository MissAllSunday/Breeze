import React from 'react'
import Comment from './Comment'
import { commentType, commentList } from 'breezeTypes'
import Loading from './Loading'
import { deleteComment } from '../api/CommentApi'

export default class CommentList extends React.Component<any, any> {
  constructor (props: { comments: commentList }) {
    super(props)
    this.state = {
      list: {
        isLoading: true,
        data: []
      }
    }
  }

  updateState (newData: Object) {
    const list = { ...this.state.list, ...newData }
    this.setState({ list })
  }

  componentDidMount () {
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

  onRemoveComment (comment: commentType) {
    deleteComment(comment.id).then((response) => {
      console.log(response)
      return

      if (response.status === 204) {
        const tmpCommentList = this.state.list.data

        delete tmpCommentList[comment.id]

        this.updateState({
          data: tmpCommentList
        })
      } else {
        // show some error
      }
    }).catch(function (error) {
      if (error.response) {
        console.log(error.response.data)
        console.log(error.response.status)
        console.log(error.response.headers)
      }
    })
  }

  render () {
    return (
      this.state.isLoading
        ? <Loading />
        : <ul className="comments">
				{this.state.list.data}
			</ul>
    )
  }
}
