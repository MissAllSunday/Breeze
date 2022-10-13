import * as React from 'react'
import Avatar from './Avatar'
import { userDataType } from 'breezeTypes'

const UserInfo: React.FunctionComponent<userDataType> = (props: userDataType) => {
  return <div className="breeze_summary floatleft">
    <div className="roundframe flow_auto">
      <Avatar
        url={props.avatar.url} />
      <h3 className="breeze_name">
        memberOnline
        member name color
      </h3>
      <p className="breeze_title">
        primary/post group
      </p>
      <p className="breeze_title">
        group icons
      </p>
      <p className="breeze_description">
        blurb
      </p>
      <div className="breeze_mood">
      </div>
    </div>
  </div>
}

export default UserInfo
