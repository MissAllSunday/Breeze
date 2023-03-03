import { baseUrl, baseConfig } from './Api'
import { commentList } from 'breezeTypes'
import smfVars from '../DataSource/SMF'

interface ServerCommentData {
  message: string
  content: commentList
}

interface ServerDeleteComment {
  message: string
  content: object
}

const action = 'breezeComment'

export const postComment = async (commentParams: object): Promise<ServerCommentData> => {
  const postComment = await fetch(baseUrl(action, 'postComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      ...commentParams,
      userId: smfVars.userId
    }))
  })

  return await postComment.ok
    ? await postComment.json()
    : await postComment.json().then(errorResponse => { throw Error(errorResponse.message) })
}

export const deleteComment = async (commentId: number): Promise<ServerDeleteComment> => {
  const deleteComment = await fetch(baseUrl(action, 'deleteComment'), {
    method: 'POST',
    body: JSON.stringify(baseConfig({
      id: commentId,
      userId: smfVars.userId
    }))
  })

  return await deleteComment.ok
    ? await deleteComment.json()
    : await deleteComment.json().then(errorResponse => { throw Error(errorResponse) })
}
