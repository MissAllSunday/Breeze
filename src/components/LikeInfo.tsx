import { LikeInfoProps, LikeInfoState } from 'breezeTypes';
import React, { useCallback, useEffect, useState } from 'react';
import toast from 'react-hot-toast';

import { getLikeInfo, ServerLikeInfoData } from '../api/LikeApi';
import Modal from './Modal';
import Avatar from './user/Avatar';

const LikeInfo: React.FunctionComponent<LikeInfoProps> = (props: LikeInfoProps) => {
  const [info, setInfo] = useState<LikeInfoState[] | null>(null);
  const [showInfo, setShowInfo] = useState(false);

  const onClose = useCallback(
    () => {
      setShowInfo(false);
    },
    [],
  );

  useEffect(() => {
    setShowInfo(info !== null);
  }, [info]);

  const handleInfo = useCallback(
    () => {
      if (info !== null) {
        setShowInfo(true);
        return;
      }

      getLikeInfo(props.item).then((response: ServerLikeInfoData) => {
        setInfo(response.content);
      }).catch((exception) => {
        toast.error(exception.toString());
      });
    },
    [props, info],
  );

  const infoBody = (
    <ul id="likes" data-testid="likes">
      {info?.map((likeInfo: LikeInfoState) => (
        <li key={likeInfo.timestamp} data-testid={likeInfo.timestamp}>
          <Avatar
            href={likeInfo.profile.avatar.url}
            userName={likeInfo.profile.username}
          />
          <span className="like_profile">
            <span dangerouslySetInnerHTML={{ __html: likeInfo.profile.link_color }} />
            <span className="description">{likeInfo.profile.group}</span>
          </span>
          <span className="floatright like_time" dangerouslySetInnerHTML={{ __html: likeInfo.timestamp }} />
        </li>
      ))}
    </ul>
  );

  const infoHeader = (`${String.fromCodePoint(128077)} ${props.item.additionalInfo.text}`);
  const infoText = props.item.count > 0
    ? (
      <span className="like_count smalltext pointer_cursor" onClick={handleInfo} data-testid="likesInfo">
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

export default LikeInfo;
