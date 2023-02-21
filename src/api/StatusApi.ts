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

export const getStatus = async (type: string): Promise<statusListType> => {
  const getStatus = await fetch(baseUrl(action, type), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      wallId: smfVars.wallId
    }))
  })

  return getStatus
}

export const deleteStatus = async (statusId: number): Promise<Response> => {
  const deleteStatus = await fetch(baseUrl(action, 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId
    }))
  })

  return deleteStatus
}

export const postStatus = async (content: string): Promise<ServerPostStatusResponse> => {
  const postStatus = await fetch(baseUrl(action, 'postStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      wallId: smfVars.wallId,
      userId: smfVars.userId,
      body: content
    }))
  })

  return await postStatus.json()
}
