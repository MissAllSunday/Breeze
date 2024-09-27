import '@testing-library/jest-dom';

import { render, screen } from '@testing-library/react';
import { CommentType } from 'breezeTypesComments';
import { PermissionsContextType } from 'breezeTypesPermissions';
import React from 'react';

import { comments } from '../__fixtures__/comments';
import permissions from '../__fixtures__/permissions';
import { PermissionsContext } from '../context/PermissionsContext';
import smfTextVars from '../DataSource/Txt';
import Comment  from './Comment';

function act(
  overwritePermissions?:Partial<PermissionsContextType>,
  custom?:Partial<CommentType>,
  onRemoved = true) {

  const actOnRemoved = () => onRemoved;
  const customComment = { ...comments.basic, ...custom };

  return render(<PermissionsContext.Provider value={overwritePermissions ? permissions.custom(overwritePermissions) : permissions.basic}>
    <Comment comment={customComment} removeComment={actOnRemoved} />
  </PermissionsContext.Provider>);
}

beforeAll(()=> {
  window.confirm = jest.fn(() => true);
});

describe('Rendering Comment component', () => {
  it('renders with default comment data', () => {
    const { container } = act();

    expect(container.firstChild).toHaveClass('comment');
  });
  it('renders the comment content', () => {
    const { container } = act();

    expect(container.getElementsByClassName('content')[0].textContent).toEqual(comments.basic.body);
  });
});

describe('Deleting a comment', () => {
  it('does not show delete button when user does not have permissions', () => {
    act();
    const spanElement = screen.queryByTitle(smfTextVars.general.delete);
    expect(spanElement).not.toBeInTheDocument();
  });
  it('show delete button when user does have permissions', () => {
    act({ Comments:{ delete:true, edit:true, post:true } });
    const spanElement = screen.queryByTitle(smfTextVars.general.delete);
    expect(spanElement).toBeInTheDocument();
  });
});
