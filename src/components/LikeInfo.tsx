import type { LikeProps, LikeType } from 'breezeTypesLikes';
import type React from 'react';
import { useCallback, useState } from 'react';

import { Modal } from './Modal';
import Avatar from './user/Avatar';

export const LikeInfo: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [showInfo, setShowInfo] = useState(false);

  const onClose = useCallback(
    () => {
      setShowInfo(false);
    },
    [],
  );

  const infoBody = props.likeInfo.likes && (
    <ul id="likes" data-testid="likes">
      {Object.values(props.likeInfo.likes).map((like: LikeType) => (
        <li key={like.userData.id}>
          <Avatar
            href={like.userData.avatar.url}
            userName={like.userData.username}
          />
          <span className="like_profile">
            <span dangerouslySetInnerHTML={{ __html: like.userData.link_color }}/>
            <span className="description">{like.userData.group}</span>
          </span>
          <span className="like_time">{like.likeTime}</span>
        </li>
      ))}
    </ul>
  );

  const infoHeader = (`${String.fromCodePoint(128077)} ${props.likeInfo.text}`);
  const infoText = props.likeInfo.count > 0
    ? (
      <button
        type="button"
        className="like_count smalltext pointer_cursor"
        onClick={() => setShowInfo(true)}
        data-testid="likesInfo">
        {props.likeInfo.text}
      </button>
    )
    : props.likeInfo.text;

  return (
    <>
      {infoText}
      <Modal
        onClose={onClose}
        show={showInfo}
        content={{
          header: infoHeader,
          body: infoBody,
        }}
      />
    </>
  );
};
