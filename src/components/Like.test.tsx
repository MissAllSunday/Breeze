import '@testing-library/jest-dom';
import type { LikeInfoType } from 'breezeTypesLikes';
import type { PermissionsContextType } from 'breezeTypesPermissions';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest'

import { likesInfo } from '../__fixtures__/likesInfo';
import permissions from '../__fixtures__/permissions';
import { PermissionsContext } from '../context/PermissionsContext';
import { Like } from './Like';

const MOCK_LIKE_INFO_ITEM = likesInfo.basic;

vi.mock('./LikeInfo', () => ({ LikeInfo: () => 'mocked like info' }));
vi.mock('../api/Like/Post', ()=> (likesInfo.custom({ count: 1, alreadyLiked: true })));

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
  window.confirm = vi.fn();
  vi.resetAllMocks();
  vi.clearAllMocks();
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
    act(true);

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
