import { LikeInfoProps } from 'breezeTypesLikes';
import { UserDataType } from 'breezeTypesUser';
import React, { useCallback, useState } from 'react';

import { Modal } from './Modal';
import Avatar from './user/Avatar';

export const LikeInfo: React.FunctionComponent<LikeInfoProps> = (props: LikeInfoProps) => {
  const [showInfo, setShowInfo] = useState(false);

  const onClose = useCallback(
    () => {
      setShowInfo(false);
    },
    [],
  );

  const infoBody = (
    <ul id="likes" data-testid="likes">
      {props.item.additionalInfo.usersData?.map((userData: UserDataType) => (
        <li key={userData.id}>
          <Avatar
            href={userData.avatar.url}
            userName={userData.username}
          />
          <span className="like_profile">
            <span dangerouslySetInnerHTML={{ __html: userData.link_color }}/>
            <span className="description">{userData.group}</span>
          </span>
        </li>
      ))}
    </ul>
  );

  const infoHeader = (`${String.fromCodePoint(128077)} ${props.item.additionalInfo.text}`);
  const infoText = props.item.count > 0
    ? (
      <span className="like_count smalltext pointer_cursor" data-testid="likesInfo">
        {props.item.additionalInfo.text}
      </span>
    )
    : props.item.additionalInfo.text;

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
