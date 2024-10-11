import '@testing-library/jest-dom';

import { render, screen } from '@testing-library/react';
import { AvatarDataType } from 'breezeTypesUser';
import React from 'react';

import Avatar from './Avatar';

const MOCK_AVATAR_PROPS:AvatarDataType = {
  href: 'href',
  userName: 'userName',
};

function act(avatarProps?: Partial<AvatarDataType>) {

  const props:AvatarDataType = { ...MOCK_AVATAR_PROPS, ...avatarProps };

  return render(<Avatar customClassName={props.customClassName} href={props.href} userName={props.userName} />);
}

describe('Rendering avatar component', () => {
  it('it has an specific src', async () => {
    act();
    const imgElement = screen.queryByAltText(MOCK_AVATAR_PROPS.userName);

    expect(imgElement).toHaveAttribute('src', MOCK_AVATAR_PROPS.href);
  });

  it('it has an specific alt', async () => {
    act();
    const imgElement = screen.queryByAltText(MOCK_AVATAR_PROPS.userName);
    expect(imgElement).toHaveAttribute('alt', MOCK_AVATAR_PROPS.userName);
  });
});

describe('When using a custom class', () => {
  it('render avatar with a custom class name', async () => {
    act({ customClassName: 'customClass' });
    const imgElement = screen.queryByAltText(MOCK_AVATAR_PROPS.userName);
    expect(imgElement).toHaveClass('customClass');
  });
});
describe('When not using custom class', () => {
  it('render avatar with the default class name', async () => {
    act();
    const imgElement = screen.queryByAltText(MOCK_AVATAR_PROPS.userName);
    expect(imgElement).toHaveClass('avatar');
  });
});
