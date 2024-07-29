import { LikeProps, LikeType } from 'breezeTypesLikes';
import React, { useCallback, useContext, useState } from 'react';

import { postLike, ServerLikeData } from '../api/LikeApi';
import { PermissionsContext } from '../context/PermissionsContext';
import smfVars from '../DataSource/SMF';
import { showError, showErrorMessage, showInfo } from '../utils/tooltip';
import { LikeInfo } from './LikeInfo';

export const Like: React.FunctionComponent<LikeProps> = (props: LikeProps) => {
  const [like, setLike] = useState<LikeType>(props.item);
  const permissions = useContext(PermissionsContext);

  const handleLike = useCallback(
    () => {
      function issueLike(): void {
        if (!window.confirm(smfVars.youSure)) {
          return;
        }

        postLike(like).then((response: ServerLikeData) => {

          if (Object.keys(response.content).length !== 0) {
            setLike(response.content);
            showInfo(response.message);
            return;
          }

          showErrorMessage(response.message);
        }).catch((exception) => {
          showError(exception.toString());
        });
      }
      issueLike();
    },
    [like],
  );

  return (
    permissions.isEnable.enableLikes && permissions.Forum.likesLike ? <div className="smflikebutton">
      <span onClick={handleLike} className="likeClass pointer_cursor" title={like.additionalInfo.text}>
        {String.fromCodePoint(like.alreadyLiked ? 128078 : 128077)}
      </span> | <LikeInfo item={like}/>
      </div> : null
  );
};
