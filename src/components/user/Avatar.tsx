import { avatarDataType } from 'breezeTypes'
import React from 'react'

const Avatar: React.FunctionComponent<avatarDataType> = (props: avatarDataType) => {
  return <img
    src={props.href}
    alt={props.userName}
    className="avatar"/>
}

export default Avatar
