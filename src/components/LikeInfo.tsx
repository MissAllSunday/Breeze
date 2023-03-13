import React from 'react'
import { LikeInfoProps, LikeInfoState } from 'breezeTypes'
import Avatar from './user/Avatar'

const LikeInfo: React.FunctionComponent<LikeInfoProps> = (props: LikeInfoProps) => {
  return (<div id="likes_popup">
    <div className="windowbg">
      <ul id="likes">
        {props?.items?.map((likeInfo: LikeInfoState) => (
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
      </ul>
    </div>
  </div>)
}

export default LikeInfo
