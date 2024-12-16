import '@testing-library/jest-dom';

import { responses } from '../../__fixtures__/responses';
import { baseUrl } from '../Base';
import { resolveDelete } from '../Resolvers/Delete';
import { deleteComment } from './Delete';

jest.mock('../Resolvers/Delete');
jest.mock('../Base');

global.fetch = jest.fn(() =>
  Promise.resolve({
    json: () => Promise.resolve(responses.basic),
  }),
) as jest.Mock;

describe('Delete Comment is invoked', () => {
  beforeEach(() => {
    (global.fetch as jest.Mock).mockResolvedValueOnce(responses.basic);
    (baseUrl as jest.Mock).mockReturnValue('url');
  });
  describe('and resource was deleted', () => {
    it('calls resolver', async () => {
      await deleteComment(1);
      expect(resolveDelete as jest.Mock).toHaveBeenCalledWith(responses.basic, 'Your comment was successfully deleted!');
    });
  });
});
