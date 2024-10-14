import '@testing-library/jest-dom';

import { responses } from '../../__fixtures__/responses';
import { status } from '../../__fixtures__/status';
import { showInfo } from '../../utils/tooltip';
import { resolvePost } from './Post';

const MOCK_GOOD_RESPONSE = responses.custom({
  ok: true,
  status: 201,
  json: () => Promise.resolve({
    message: 'some server error',
    content: status.basic,
  }),
});

const MOCK_BAD_RESPONSE = responses.custom({
  ok: false,
  status: 400,
  json: () => Promise.resolve({
    message: 'some server error',
    content: status.basic,
  }),
});

jest.mock('../../utils/tooltip', () => ({
  showInfo: jest.fn(() => 'some error string'),
}));

describe('resolves Post call', () => {
  describe('and resource was created', () => {
    it('shows success message', async () => {
      await resolvePost(MOCK_GOOD_RESPONSE);

      expect(showInfo as jest.Mock).toHaveBeenCalled();
    });
    it('returns created content', async () => {
      const result = await resolvePost(MOCK_GOOD_RESPONSE);

      expect(result).toBe(status.basic);
    });
  });
  describe('and resource was not created', () => {

    it('does not show success message', async () => {
      await resolvePost(MOCK_BAD_RESPONSE);

      expect(showInfo as jest.Mock).not.toHaveBeenCalled();
    });
  });
});
