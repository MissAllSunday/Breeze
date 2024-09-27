import { UserDataType } from 'breezeTypesUser';



const basic:UserDataType = {
  avatar: {
    href: 'https://missallsunday.com',
    image: document.createElement('img'),
    name: 'avatar',
    url: 'https://missallsunday.com',
  },
  buddies: ['2', '3'],
  custom_fields: [],
  email: 'email@domain.com',
  group: 'some group',
  group_color: '#666',
  group_icons: 'some icons',
  group_id: '666',
  href: '???',
  id: 0,
  is_activated: '1',
  is_banned: false,
  is_buddy: false,
  is_guest: false,
  is_reverse_buddy: false,
  last_login_timestamp: '1695828664',
  link: '<a href="#">link</a>',
  link_color: '<a href="#" style="color: rgb(255, 136, 57);">color link</a>',
  name: 'name',
  name_color: '<span style="color: rgb(255, 136, 57);">color name</span>',
  online: {
    href: '#',
    is_online: false,
    label: 'online',
    link: document.createElement('a'),
    member_online_text: 'online',
    text: 'online',
  },
  signature: 'this is a signature',
  title: 'title',
  username: 'username',
  username_color: document.createElement('span'),
};

const custom = (replace: Partial<UserDataType>) => {
  return { ...basic, ...replace };
};

export const userData = { basic, custom };
