import axios from 'axios'
import { baseUrl, baseConfig } from './Api'
import { statusType } from 'breezeTypes'
import smfVars from '../DataSource/SMF'

export interface ServerStatusResponse {
  data: ServerCommentData
}

interface ServerCommentData {
  users: object
  status: statusType[]
}

const action = 'breezeComment'

export const postComment = async (commentParams: object) => {
  return await axios.post<ServerCommentData>(
    baseUrl(action, 'postComment', [{
      ...commentParams,
      userId: smfVars.userId
    }]),
    baseConfig()
  )
}

export const deleteComment = async (commentId: number) => {
  // SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
  return await axios.post(baseUrl(action, 'deleteComment'), baseConfig({
    id: commentId,
    userId: smfVars.userId
  }))
}
