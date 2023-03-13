import React, { useCallback, useState } from 'react'
import { getLikeInfo, postLike, ServerLikeData, ServerLikeInfoData } from '../api/LikeApi'
import { LikeProps, likeType, LikeInfoState } from 'breezeTypes'
import toast from 'react-hot-toast'
import smfVars from '../DataSource/SMF'
import LikeInfo from './LikeInfo'
import Modal from './Modal'

const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<likeType>(props.item)
  const [info, setInfo] = useState<LikeInfoState[] | null>(null)
  const [showInfo, setShowInfo] = useState(false)

  const handleInfo = useCallback(
    () => {
      setShowInfo(true)
      function likeInfo (): void {
        getLikeInfo(props.item).then((response: ServerLikeInfoData) => {
          setInfo(response.content)
        }).catch(exception => {
          toast.error(exception.toString())
        })
      }
      likeInfo()
    },
    [props]
  )

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
    <div>
      <Modal
        isShowing={showInfo}
        header={like.additionalInfo.text}
        body={<LikeInfo items={info}></LikeInfo>}></Modal>
      <div className="smflikebutton">
      <span onClick={handleLike} className='likeClass pointer_cursor' >
        {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
      </span>
        <span className="like_count smalltext">
         | <span onClick={handleInfo}>{like.additionalInfo.text}</span>
      </span>
      </div>
    </div>
  )
}

export default Like
