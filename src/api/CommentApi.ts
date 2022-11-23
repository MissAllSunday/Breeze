import axios, { AxiosResponse } from 'axios'
import { baseUrl, baseConfig } from './Api'
import { commentList } from 'breezeTypes'
import smfVars from '../DataSource/SMF'

export interface ServerStatusResponse {
  data: ServerCommentData
}

interface ServerCommentData {
  message: string
  content: commentList
}

const action = 'breezeComment'

export const postComment = async (commentParams: object): Promise<AxiosResponse<ServerCommentData>> => {
  return await axios.post<ServerCommentData>(
    baseUrl(action, 'postComment'),
    baseConfig({
      ...commentParams,
      userId: smfVars.userId
    })
  )
}

export const deleteComment = async (commentId: number): Promise<AxiosResponse<any>> => {
  // SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
  return await axios.post(baseUrl(action, 'deleteComment'), baseConfig({
    id: commentId,
    userId: smfVars.userId
  }))
}
