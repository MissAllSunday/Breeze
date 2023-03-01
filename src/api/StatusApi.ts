import { baseUrl, baseConfig } from './Api'
import { statusListType } from 'breezeTypes'
import smfVars from '../DataSource/SMF'

export interface ServerDeleteStatusResponse {
  content: object
  message: string
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

export const deleteStatus = async (statusId: number): Promise<ServerDeleteStatusResponse> => {
  const deleteStatus = await fetch(baseUrl(action, 'deleteStatus'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: statusId,
      userId: smfVars.userId
    }))
  })

  return await deleteStatus.ok
    ? await deleteStatus.json()
    : await deleteStatus.json().then(errorResponse => { throw Error(errorResponse.message) })
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

  return await postStatus.ok
    ? await postStatus.json()
    : await postStatus.json().then(errorResponse => { throw Error(errorResponse.message) })
}
