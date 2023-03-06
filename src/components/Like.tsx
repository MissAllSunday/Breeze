import React, { useCallback, useState } from 'react'
import { postLike, ServerLikeData } from '../api/LikeApi'
import { LikeProps, likeType } from 'breezeTypes'
import toast from 'react-hot-toast'
import smfVars from '../DataSource/SMF'

const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<likeType>(props.item)

  const handleLike = useCallback(
    () => {
      function issueLike (): void {
        if (!confirm(smfVars.youSure)) {
          return
        }

        postLike(like).then((response: ServerLikeData) => {
          toast.success(response.message)
          setLike(response.content)
        }).catch(exception => {
          toast.error(exception.toString())
        })
      }
      issueLike()
    },
    [like]
  )

  return (
    <div className="smflikebutton">
      <span onClick={handleLike} className='likeClass pointer_cursor' >
        {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
      </span>
      <span className="like_count smalltext">
         | {like.additionalInfo.text}
      </span>
    </div>
  )
}

export default Like
