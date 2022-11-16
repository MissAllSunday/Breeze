import axios, { AxiosResponse } from 'axios'
import { baseUrl, baseConfig } from './Api'
import { statusListType } from 'breezeTypes'
import smfVars from '../DataSource/SMF'

export interface ServerStatusResponse {
  data: statusListType
}

export interface ServerPostStatusResponse {
  content: statusListType
  message: string
  type: string
}

const action = 'breezeStatus'

export const getByProfile = async (): Promise<AxiosResponse<statusListType>> => {
  return await axios.post<statusListType>(
    baseUrl(action, 'statusByProfile'),
    baseConfig({
      wallId: smfVars.wallId
    })
  )
}

export const deleteStatus = async (statusId: number): Promise<any> => {
  // SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
  return await axios.post(baseUrl(action, 'deleteStatus'), baseConfig({
    id: statusId,
    userId: smfVars.userId
  }))
}

export const postStatus = async (content: string): Promise<AxiosResponse<ServerPostStatusResponse>> => {
  return await axios.post<ServerPostStatusResponse>(baseUrl(action, 'postStatus'), baseConfig({
    wallId: smfVars.wallId,
    userId: smfVars.userId,
    body: content
  }))
}
