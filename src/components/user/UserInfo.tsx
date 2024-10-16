import { UserInfoProps } from 'breezeTypesUser';
import * as React from 'react';

import Avatar from './Avatar';

const UserInfo: React.FunctionComponent<UserInfoProps> = (props: UserInfoProps) => (
  <ul className="user_info flow_auto">
    <li dangerouslySetInnerHTML={{ __html: props.userData.link_color }} />
    <li className="avatar">
      <Avatar
        href={props.userData.avatar.url}
        userName={props.userData.username}
      />
    </li>

    <li className="postgroup">
      {props.userData.group}
    </li>
    <li className="icons" dangerouslySetInnerHTML={{ __html: props.userData.group_icons }} />
    <li className="breeze_description">
      {props.userData.title}
    </li>
  </ul>
);

export default UserInfo;
