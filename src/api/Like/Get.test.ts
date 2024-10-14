import '@testing-library/jest-dom';

import likes from '../../__fixtures__/likes';
import { baseUrl } from '../Base';
import { getLikeInfo } from './Get';
import { showError } from '../../utils/tooltip';
import { resolveGet } from '../Resolvers/Get';

const MOCK_LIKE_ITEM = likes.basic;
const MOCK_RESPONSE = { data: {  } };

// global.fetch = jest.fn(() =>
//   Promise.resolve({
//     json: () => Promise.resolve({ rates: { CAD: 1.42 } }),
//   })
// );
jest.mock('../Base', () => ({
  baseUrl: jest.fn(() => 'some url'),
}));

jest.mock('../../utils/tooltip', () => ({
  showError: jest.fn(() => 'some error'),
}));

jest.mock('../Resolvers/Get', () => ({
  resolveGet: jest.fn(),
}));

describe('get like info', () => {
  it('calls base url', async () => {
    const result = getLikeInfo(MOCK_LIKE_ITEM);

    expect(baseUrl as jest.Mock).toHaveBeenCalled();
  });

  describe('and sent wrong params', () => {

    it('shows error message', async () => {
      jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.reject(new Error('something')));
      await getLikeInfo(MOCK_LIKE_ITEM);

      expect(showError as jest.Mock).toHaveBeenCalled();
    });
  });
  describe('and sent correct params', () => {

    it('calls resolver', async () => {
      jest.spyOn(global, 'fetch').mockResolvedValueOnce(Promise.resolve(new Response()));
      await getLikeInfo(MOCK_LIKE_ITEM);

      expect(resolveGet as jest.Mock).toHaveBeenCalled();
    });
  });
});
