import { baseUrl, baseConfig } from './Api'
import { likeType } from 'breezeTypes'
import SmfVars from '../DataSource/SMF'

export interface ServerLikeData {
  content: likeType
}

const action = 'breezeLike'

export const postLike = async (likeData: likeType): Promise<ServerLikeData> => {
  const params = {
    id_member: SmfVars.userId,
    content_type: likeData.type,
    content_id: likeData.contentId
  }

  const response = await fetch(baseUrl(action, 'like'), {
    method: 'POST',
    body: JSON.stringify(baseConfig(params))
  })

  return await response.json()
}
