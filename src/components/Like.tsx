import { LikeProps, LikeType } from 'breezeTypesLikes';
import React, { useCallback, useState } from 'react';
import toast from 'react-hot-toast';

import { postLike, ServerLikeData } from '../api/LikeApi';
import smfVars from '../DataSource/SMF';
import LikeInfo from './LikeInfo';

const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<LikeType>(props.item);

  const handleLike = useCallback(
    () => {
      function issueLike(): void {
        if (!window.confirm(smfVars.youSure)) {
          return;
        }

        postLike(like).then((response: ServerLikeData) => {
          toast.success(response.message);
          setLike(response.content);
        }).catch((exception) => {
          toast.error(exception.toString());
        });
      }
      issueLike();
    },
    [like],
  );

  return (
    <div className="smflikebutton">
      <span onClick={handleLike} className="likeClass pointer_cursor" title={like.type}>
        {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
      </span>
      {' '}
      |
      <LikeInfo item={like} />
    </div>
  );
};

export default Like;
