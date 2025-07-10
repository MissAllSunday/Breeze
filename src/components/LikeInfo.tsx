import { LikeProps, UsersLikeInfoType } from 'breezeTypesLikes';
import React, { useCallback, useState } from 'react';

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

  const infoBody = props.item.additionalInfo.usersLikeInfo && (
    <ul id="likes" data-testid="likes">
      {Object.values(props.item.additionalInfo.usersLikeInfo).map((userLikeInfo: UsersLikeInfoType) => (
        <li key={userLikeInfo.userData.id}>
          <Avatar
            href={userLikeInfo.userData.avatar.url}
            userName={userLikeInfo.userData.username}
          />
          <span className="like_profile">
            <span dangerouslySetInnerHTML={{ __html: userLikeInfo.userData.link_color }}/>
            <span className="description">{userLikeInfo.userData.group}</span>
          </span>
          <span className="like_time">{userLikeInfo.likeTime}</span>
        </li>
      ))}
    </ul>
  );

  const infoHeader = (`${String.fromCodePoint(128077)} ${props.item.additionalInfo?.text}`);
  const infoText = props.item.count > 0
    ? (
      <span className="like_count smalltext pointer_cursor" onClick={() => setShowInfo(true)} data-testid="likesInfo">
        {props.item.additionalInfo?.text}
      </span>
    )
    : props.item.additionalInfo?.text;

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
