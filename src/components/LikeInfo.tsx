import React, { useCallback, useEffect, useState } from 'react'
import { LikeInfoProps, LikeInfoState } from 'breezeTypes'
import Avatar from './user/Avatar'
import Modal from './Modal'
import { getLikeInfo, ServerLikeInfoData } from '../api/LikeApi'
import toast from 'react-hot-toast'

const LikeInfo: React.FunctionComponent<LikeInfoProps> = (props: LikeInfoProps) => {
  const [info, setInfo] = useState<LikeInfoState[] | null>(null)
  const [showInfo, setShowInfo] = useState(false)

  const onClose = useCallback(
    () => {
      setShowInfo(false)
    },
    []
  )

  useEffect(() => {
    setShowInfo(info !== null)
  }, [info])

  const handleInfo = useCallback(
    () => {
      getLikeInfo(props.item).then((response: ServerLikeInfoData) => {
        setInfo(response.content)
      }).catch(exception => {
        toast.error(exception.toString())
      })
    },
    [props]
  )

  const infoBody = (
      <ul id="likes">
        {info?.map((likeInfo: LikeInfoState) => (
          <li key={likeInfo.timestamp}>
            <Avatar
              href={likeInfo.profile.avatar.url}
              userName={likeInfo.profile.username}/>
            <span className="like_profile">
                 <span dangerouslySetInnerHTML={{ __html: likeInfo.profile.link_color }} />
                <span className="description">{likeInfo.profile.group}</span>
              </span>
            <span className="floatright like_time">{likeInfo.timestamp}</span>
          </li>
        ))}
      </ul>)

  const infoHeader = (String.fromCodePoint(128077) + ' ' + props.item.additionalInfo.text)

  return (<>
      <span className="like_count smalltext pointer_cursor" onClick={handleInfo}>{props.item.additionalInfo.text}</span>
      <Modal
        onClose={onClose}
        show={showInfo}
        content={{
          header: infoHeader,
          body: infoBody
        }}></Modal>
  </>
  )
}

export default LikeInfo
