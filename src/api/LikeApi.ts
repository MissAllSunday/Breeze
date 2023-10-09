import { LikeInfoState, likeType } from 'breezeTypes'

import SmfVars from '../DataSource/SMF'
import { baseConfig,baseUrl } from './Api'

export interface ServerLikeData {
  content: likeType
  message: string
}

export interface ServerLikeInfoData {
  message: string
  content: LikeInfoState[]
}

const action = 'breezeLike'

export const postLike = async (likeData: likeType): Promise<ServerLikeData> => {
  const params = {
    id_member: SmfVars.userId,
    content_type: likeData.type,
    content_id: likeData.contentId
  }

  const like = await fetch(baseUrl(action, 'like'), {
    method: 'POST',
    body: JSON.stringify(baseConfig(params))
  })

  return await like.ok
    ? await like.json()
    : await like.json().then(errorResponse => { throw Error(errorResponse.message) })
}

export const getLikeInfo = async (like: likeType): Promise<ServerLikeInfoData> => {
  const params = {
    content_type: like.type,
    content_id: like.contentId
  }

  const likeInfo = await fetch(baseUrl(action, 'info'), {
    method: 'POST',
    body: JSON.stringify(baseConfig(params))
  })

  return await likeInfo.ok
    ? await likeInfo.json()
    : await likeInfo.json().then(errorResponse => { throw Error(errorResponse.message) })
}
