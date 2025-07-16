import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { LikeInfoType } from 'breezeTypesLikes';
import { PermissionsContextType } from 'breezeTypesPermissions';
import React from 'react';

import { likesInfo } from '../__fixtures__/likesInfo';
import permissions from '../__fixtures__/permissions';
import { PermissionsContext } from '../context/PermissionsContext';
import { Like } from './Like';

const MOCK_LIKE_INFO_ITEM = likesInfo.basic;

jest.mock('./LikeInfo', () => ({ LikeInfo: () => 'mocked like info' }));
jest.mock('../api/Like/Post', ()=> jest.fn());

function act(setPermissionsTo: boolean, overwriteLikeItem?: Partial<LikeInfoType>) {

  const permissionsPartial:Partial<PermissionsContextType> = {
    isEnable: { enableLikes: setPermissionsTo },
    Forum: {
      likesLike: setPermissionsTo,
      adminForum: false,
      profileView: false,
    },
  };
  const customPermissions:PermissionsContextType = { ...permissions.basic, ...permissionsPartial };
  const likeInfo:LikeInfoType = { ...MOCK_LIKE_INFO_ITEM, ...overwriteLikeItem };

  return render(<PermissionsContext.Provider value={customPermissions}>
    <Like likeInfo={likeInfo} />
  </PermissionsContext.Provider>);
}

beforeAll(()=> {
  window.confirm = jest.fn();
});

describe('When like setting is disable and permissions are not granted', () => {
  it('does not render the Like component', async () => {
    act(false);
    const spanElement = screen.queryByTitle(MOCK_LIKE_INFO_ITEM.text);
    expect(spanElement).not.toBeInTheDocument();
  });
});

describe('When like setting is enable and permissions are granted', () => {
  it('render the Like component', async () => {
    const { container } = act(true);

    expect(container.firstChild).toHaveClass('smflikebutton');
  });

  it('shows span tag', async () => {
    const { container } = act(true);

    const spanElement = screen.queryByTitle('Like');
    expect(spanElement).toBeInTheDocument();
  });

  describe('When the user likes something', () => {
    it('text changes to liked', async () => {
      act(true);
      const spanElement = screen.getByTitle('Like');

      await waitFor(() => {
        userEvent.click(spanElement);
      });

      await waitFor(() => expect(spanElement).toHaveTextContent(String.fromCodePoint(128077)));
    });
  });
});
