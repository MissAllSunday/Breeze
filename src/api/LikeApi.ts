import { baseUrl, baseConfig } from './Api'
import { likeType } from 'breezeTypes'
import SmfVars from '../DataSource/SMF'

export interface ServerLikeData {
  content: likeType
  message: string
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
