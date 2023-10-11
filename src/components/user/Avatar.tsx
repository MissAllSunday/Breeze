import { AvatarDataType } from 'breezeTypes';
import React from 'react';

const Avatar: React.FunctionComponent<AvatarDataType> = (props: AvatarDataType) => (
  <img
    src={props.href}
    alt={props.userName}
    className="avatar"
  />
);

export default Avatar;
