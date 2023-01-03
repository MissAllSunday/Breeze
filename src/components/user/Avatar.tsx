import React from 'react'
import { avatarDataType } from 'breezeTypes'

const Avatar: React.FunctionComponent<avatarDataType> = (props: avatarDataType) => {
  return <img
    src={props.href}
    alt={props.userName}
    className="avatar"/>
}

export default Avatar
