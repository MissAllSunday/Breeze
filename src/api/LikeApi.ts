import axios, { AxiosResponse } from 'axios'
import { baseUrl, baseConfig } from './Api'
import { likeType } from 'breezeTypes'

export interface ServerLikeResponse {
  data: ServerLikeData
}

export interface ServerLikeData {
  content: likeType[]
}

const action = 'breezeLike'

export const postLike = async (likeData: object): Promise<AxiosResponse<ServerLikeData>> => {
  return await axios.post<ServerLikeData>(
    baseUrl(action, 'like'),
    baseConfig(likeData)
  )
}
