import {
  ServerStatusResponse,
  getStatus,
  deleteStatus,
  postStatus,
  ServerPostStatusResponse
} from './api/StatusApi'
import React from 'react'
import { statusType, statusListType, commentType, commentList, wallProps } from 'breezeTypes'
import Loading from './components/Loading'
import Editor from './components/Editor'
import { AxiosResponse } from 'axios'
import { StatusList } from './components/StatusList'
import { deleteComment } from './api/CommentApi'

export default class Wall extends React.Component<any, any> {
  list: statusType[] = []
  constructor (props: wallProps) {
    super(props)
    this.state = {
      isLoading: true
    }
  }

  updateState (newData: object): void {
    const newState = { ...this.state, ...newData }

    this.setState(newState)
  }

  componentDidMount (): void {
    getStatus(this.props.wallType)
      .then((response: ServerStatusResponse) => {
        const newStatus: statusListType = Object.values(response.data.content)
        this.list = newStatus.map((status: statusType) => {
          status.comments = Object.values(status.comments)

          return status
        })

        this.updateState({
          isLoading: false
        })
      })
      .catch(exception => {
      })
  }

  removeStatus = (status: statusType): void => {
    this.updateState({
      isLoading: true
    })

    deleteStatus(status.id).then((response) => {
      if (response.status !== 204) {
        // Show some error message
        return
      }

      this.updateState({
        isLoading: false
      })
      this.list = this.list.filter(function (statusListItem: statusType) {
        return statusListItem.id !== status.id
      })
    }).catch(function (error) {
      console.log(error.response.data)
      console.log(error.response.status)
      console.log(error.response.headers)
    })
  }

  removeComment = (status: statusType, comment: commentType): void => {
    this.setState({
      isLoading: true
    })

    deleteComment(comment.id).then((response) => {
      if (response.status !== 204) {
        return
      }

      this.list = this.list.map(function (statusListItem: statusType) {
        statusListItem.comments = statusListItem.comments.filter(function (commentListItem: commentType) {
          return commentListItem.id !== comment.id
        })
        return statusListItem
      })

      this.setState({
        isLoading: false
      })
    }).catch(function (error) {
      console.log(error.response.data)
      console.log(error.response.status)
      console.log(error.response.headers)
      // show some error message
    })
  }

  onCreateComment = (commentList: commentList, statusID: number): void => {
    this.list = this.list.map(function (statusListItem: statusType) {
      if (statusListItem.id === statusID) {
        statusListItem.comments = [...statusListItem.comments, commentList.pop()]
      }

      return statusListItem
    })

    this.updateState({
      isLoading: false
    })
  }

  onNewStatus = (content: string): void => {
    this.updateState({
      isLoading: true
    })

    postStatus(content).then((response: AxiosResponse<ServerPostStatusResponse>) => {
      if (response.status !== 201) {
        return
      }

      // this.list = [...this.list, ...response.data.content]

      this.updateState({
        isLoading: false
      })
    }).catch((error) => {
      console.log(error.response.data)
      console.log(error.response.status)
      console.log(error.response.headers)
      this.updateState({
        isLoading: false
      })
    })
  }

  render (): JSX.Element {
    const isLoading = this.state.isLoading
    return (<div>
      {isLoading === true
        ? <Loading />
        : <>
          <div>
            {isLoading === true
              ? <Loading />
              : <Editor saveContent={this.onNewStatus} />
            }
          </div>
          <StatusList
          statusList={this.list}
          removeStatus={this.removeStatus}
          removeComment={this.removeComment}
          onCreateComment={this.onCreateComment}/>
        </>}
    </div>)
  }
}
