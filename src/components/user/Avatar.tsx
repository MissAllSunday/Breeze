import { AvatarDataType } from 'breezeTypesUser';
import React from 'react';

const Avatar: React.FunctionComponent<AvatarDataType> = (props: AvatarDataType) => (
  <img
    src={props.href}
    alt={props.userName}
    className={typeof props.customClassName !== 'undefined' ? props.customClassName : 'avatar'}
  />
);

export default Avatar;
