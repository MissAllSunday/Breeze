import React, { useCallback, useState } from 'react'
import { ServerLikeResponse, postLike } from '../api/LikeApi'
import { LikeProps, likeType } from 'breezeTypes'

const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<likeType>(props.item)

  const handleLike = useCallback(
    () => {
      function issueLike (): void {
        postLike(like).then((response: ServerLikeResponse) => {
          setLike(response.data.content)
        }).catch(exception => {
          console.log(exception)
        })
      }
      issueLike()
    },
    [like]
  )

  return (
    <div className="smflikebutton">
    <span onClick={handleLike} className='likeClass' >
      {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
    </span>
      <div className="like_count smalltext">
        {}
      </div>
    </div>
  )
}

export default Like
