declare module 'breezeTypesUser' {
  interface UserDataType {
    avatar: {
      href: string
      image: HTMLImageElement
      name: string
      url: string
    }
    buddies: string[]
    custom_fields: string[]
    email: string
    group: string
    group_color: string
    group_icons: string
    group_id: string
    href: string
    id: number
    is_activated: string
    is_banned: boolean
    is_buddy: boolean
    is_guest: boolean
    is_reverse_buddy: boolean
    last_login_timestamp: string
    link: string
    link_color: string
    name: string
    name_color: string
    online: {
      href: string
      is_online: boolean
      label: string
      link: HTMLAnchorElement
      member_online_text: string
      text: string
    }
    signature: string
    title: string
    username: string
    username_color: HTMLSpanElement
  }

  interface AvatarDataType {
    href: string
    userName: string
  }
  interface UserInfoProps {
    userData: UserDataType
  }
}

module.exports = {
  UserDataType,
};
