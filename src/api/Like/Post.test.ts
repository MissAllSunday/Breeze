import '@testing-library/jest-dom';

import likes from '../../__fixtures__/likes';
import { showError } from '../../utils/tooltip';
import { baseConfig, baseUrl } from '../Base';
import { resolvePost } from '../Resolvers/Post';
import { postLike } from './Post';

const MOCK_LIKE_ITEM = likes.basic;

jest.mock('../Base', () => ({
  baseUrl: jest.fn(() => 'some url'),
  baseConfig: jest.fn(() => 'some config'),
}));

jest.mock('../../utils/tooltip', () => ({
  showError: jest.fn(() => 'some error'),
}));

jest.mock('../Resolvers/Post', () => ({
  resolvePost: jest.fn(),
}));

describe('posting a like', () => {
  it('calls base url', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.resolve(new Response()));
    await postLike(MOCK_LIKE_ITEM);

    expect(baseUrl as jest.Mock).toHaveBeenCalled();
  });

  it('calls base config', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.resolve(new Response()));
    await postLike(MOCK_LIKE_ITEM);

    expect(baseConfig as jest.Mock).toHaveBeenCalled();
  });

  describe('and sent correct params', () => {

    it('calls resolver', async () => {
      jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.resolve(new Response()));
      await postLike(MOCK_LIKE_ITEM);

      expect(resolvePost as jest.Mock).toHaveBeenCalled();
    });
  });
  describe('and sent wrong params', () => {

    it('shows error message', async () => {
      jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.reject(new Error('something')));
      await postLike(MOCK_LIKE_ITEM);
      expect(showError as jest.Mock).toHaveBeenCalled();
    });
  });
});
