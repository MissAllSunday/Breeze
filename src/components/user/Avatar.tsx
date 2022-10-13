import React from 'react'
import { avatarDataType } from 'breezeTypes'

const Avatar: React.FunctionComponent<avatarDataType> = (props: avatarDataType) => {
  const divStyle = {
    backgroundImage: 'url(' + props.url + ')'
  }

  return <div
      className='breeze_avatar avatar_status floatleft'
      style={divStyle}
  />
}

export default Avatar
