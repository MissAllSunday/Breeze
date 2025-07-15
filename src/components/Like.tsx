import { LikeProps, LikeType } from 'breezeTypesLikes';
import React, { useCallback, useContext, useState } from 'react';

import { postLike } from '../api/Like/Post';
import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
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

    postLike(like).then((newLikes: LikeType[]) => {
      setLike(newLikes[0]);
    }).finally(() => setIsLoading(false));
  }, [like]);

  return (
    permissions.isEnable.enableLikes && permissions.Forum.likesLike ?
      <div className="smflikebutton">
        {isLoading ? <Loading/> : ''}
        <span onClick={handleLike} className="likeClass pointer_cursor" title={ like && like.alreadyLiked ? smfTextVars.like.unlike : smfTextVars.like.like }>
          {String.fromCodePoint(like && like.alreadyLiked ? 128078 : 128077)}
        </span> { like && like.additionalInfo && <LikeInfo item={like} />}
      </div> : null
  );
};
