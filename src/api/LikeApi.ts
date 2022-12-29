import axios, { AxiosResponse } from 'axios'
import { baseUrl, baseConfig } from './Api'
import { likeType } from 'breezeTypes'
import SmfVars from '../DataSource/SMF'

export interface ServerLikeResponse {
  data: ServerLikeData
}

export interface ServerLikeData {
  content: likeType
}

const action = 'breezeLike'

export const postLike = async (likeData: likeType): Promise<AxiosResponse<ServerLikeData>> => {
  const params = {
    id_member: SmfVars.userId,
    content_type: likeData.type,
    content_id: likeData.contentId
  }
  return await axios.post<ServerLikeData>(
    baseUrl(action, 'like'),
    baseConfig(params)
  )
}
