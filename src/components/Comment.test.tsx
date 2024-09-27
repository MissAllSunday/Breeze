import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CommentType } from 'breezeTypesComments';

import { comments } from '../__fixtures__/comments';

const MOCK_COMMENT_DATA:Partial<CommentType> = comments.basic;

describe('Rendering Comment component', () => {
  it('renders with default comment data', () => {});


});
