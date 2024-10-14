import '@testing-library/jest-dom';

import { responses } from '../../__fixtures__/responses';
import { status } from '../../__fixtures__/status';
import { showError } from '../../utils/tooltip';
import { resolveGet } from './Get';

const MOCK_GOOD_RESPONSE = responses.custom({
  ok: true,
  status: 200,
  json: () => Promise.resolve({
    message: 'some server error',
    content: status.basic,
  }),
});

jest.mock('../../utils/tooltip', () => ({
  showError: jest.fn(() => 'some error string'),
}));

describe('resolves Get call', () => {
  describe('and call has a message', () => {
    it('shows error message', async () => {
      await resolveGet(MOCK_GOOD_RESPONSE);

      expect(showError as jest.Mock).toHaveBeenCalled();
    });
  });
  describe('and resource was fetched', () => {

    it('returns fetched content', async () => {
      const result = await resolveGet(MOCK_GOOD_RESPONSE);

      expect(result).toBe(status.basic);
    });
  });
});
