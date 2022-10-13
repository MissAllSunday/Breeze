import axios from 'axios'
import { baseUrl, baseConfig } from './Api'
import { likeType } from 'breezeTypes'

export interface ServerLikeResponse {
  data: ServerLikeData
}

export interface ServerLikeData {
  content: likeType[]
}

const action = 'like'

export const postLike = async (likeData: object) => {
  return await axios.post<ServerLikeData>(
    baseUrl(action, 'like'),
    baseConfig(likeData)
  )
}
