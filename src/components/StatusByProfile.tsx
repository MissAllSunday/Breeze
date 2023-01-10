import { ServerStatusResponse, getByProfile, deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import React from 'react'
import { statusType, statusListType, commentType, commentList } from 'breezeTypes'
import Loading from './Loading'
import Editor from './Editor'
import { AxiosResponse } from 'axios'
import { StatusList } from './StatusList'
import { deleteComment } from '../api/CommentApi'

export default class StatusByProfile extends React.Component<any, any> {
  constructor (props: any) {
    super(props)
    this.state = {
      list: [],
      isLoading: true
    }
  }

  updateState (newData: object): void {
    const newState = { ...this.state, ...newData }

    this.setState(newState)
  }

  componentDidMount (): void {
    getByProfile()
      .then((response: ServerStatusResponse) => {
        let newStatus: statusListType = Object.values(response.data.content)
        newStatus = newStatus.map((status: statusType) => {
          status.comments = Object.values(status.comments)

          return status
        })

        this.updateState({
          list: newStatus,
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
        list: this.state.list.filter(function (statusListItem: statusType) {
          return statusListItem.id !== status.id
        }),
        isLoading: false
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

      const newStatusList = this.state.list.map(function (statusListItem: statusType) {
        statusListItem.comments = statusListItem.comments.filter(function (commentListItem: commentType) {
          return commentListItem.id !== comment.id
        })
        return statusListItem
      })

      this.setState({
        list: newStatusList,
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
    const newStatusList = this.state.list.map(function (statusListItem: statusType) {
      if (statusListItem.id === statusID) {
        statusListItem.comments = [...statusListItem.comments, commentList.pop()]
      }

      return statusListItem
    })

    this.updateState({
      list: newStatusList,
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

      this.updateState({
        list: [...this.state.list, ...Object.values(response.data.content)],
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
          statusList={this.state.list}
          removeStatus={this.removeStatus}
          removeComment={this.removeComment}
          onCreateComment={this.onCreateComment}/>
        </>}
    </div>)
  }
}
