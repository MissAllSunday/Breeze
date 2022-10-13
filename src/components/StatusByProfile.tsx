import { ServerStatusResponse, getByProfile, deleteStatus, postStatus, ServerPostStatusResponse } from '../api/StatusApi'
import React from 'react'
import { statusType } from 'breezeTypes'
import Loading from './Loading'
import Editor from './Editor'
import { AxiosResponse } from 'axios'
import { StatusList } from './StatusList'

export default class StatusByProfile extends React.Component<any, any> {
  constructor (props: any) {
    super(props)
    this.state = {
      list: [],
      isLoading: true
    }

    this.onRemoveStatus = this.onRemoveStatus.bind(this)
    this.onNewStatus = this.onNewStatus.bind(this)
  }

  updateState (newData: object): void {
    const newState = { ...this.state, ...newData }

    this.setState(newState, function () {
      console.log(newState)
    })
  }

  componentDidMount (): void {
    getByProfile()
      .then((response: ServerStatusResponse) => {
        this.updateState({
          list: Object.values(response.data),
          isLoading: false
        })
      })
      .catch(exception => {
      })
  }

  onRemoveStatus (status: statusType): void {
    this.updateState({
      isLoading: true
    })

    deleteStatus(status.id).then((response) => {
      if (response.status !== 204) {
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

  onNewStatus (content: string): void {
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
    }).catch(function (error) {
      console.log(error.response.data)
      console.log(error.response.status)
      console.log(error.response.headers)
    }).finally(() => {
      this.updateState({
        isLoading: false
      })
    })
  }

  render (): JSX.Element {
    return (<div>
      this.state.isLoading ? <Loading /> :
      <StatusList
        statusList={this.state.list}
        onRemoveStatus={this.onRemoveStatus} />
      <Editor saveContent={this.onNewStatus} />
    </div>)
  }
}
