import { LikeProps, LikeType } from 'breezeTypesLikes';
import React, { useCallback, useContext, useState } from 'react';

import { postLike } from '../api/Like/Post';
import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import { LikeInfo } from './LikeInfo';
import Loading from './Loading';

export const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<LikeType>(props.item);
  const permissions = useContext(PermissionsContext);
  const [isLoading, setIsLoading] = useState(false);

  const handleLike = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }
    setIsLoading(true);

    postLike(like).then((newLike: LikeType) => {
      setLike(newLike);
    }).finally(() => setIsLoading(false));
  }, [like]);

  return (
    permissions.isEnable.enableLikes && permissions.Forum.likesLike ? <div className="smflikebutton">
      {isLoading ? <Loading/> : ''}
      <span onClick={handleLike} className="likeClass pointer_cursor" title={like.additionalInfo.text}>
        {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
      </span> | <LikeInfo item={like}/>
      </div> : null
  );
};
