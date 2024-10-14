import '@testing-library/jest-dom';

import { responses } from '../../__fixtures__/responses';
import { showError, showInfo } from '../../utils/tooltip';
import { resolveDelete } from './Delete';

const MOCK_BAD_RESPONSE = responses.custom({
  ok: false,
  status: 400,
  json: () => Promise.resolve({ message: 'some server error' }),
});

const MOCK_GOOD_RESPONSE = responses.custom({
  ok: true,
  status: 204,
  json: () => Promise.resolve({ message: 'some server error' }),
});

jest.mock('../../utils/tooltip', () => ({
  showError: jest.fn(() => 'some error string'),
  showInfo: jest.fn(() => 'some success string'),
}));

describe('resolves Deleting call', () => {
  describe('and resource was not deleted', () => {
    it('shows error message', async () => {
      await resolveDelete(MOCK_BAD_RESPONSE, 'success!');

      expect(showError as jest.Mock).toHaveBeenCalled();
    });
    it('returns false', async () => {
      const result = await resolveDelete(MOCK_BAD_RESPONSE, 'success!');

      expect(result).toBe(false);
    });
  });
  describe('and resource was deleted', () => {

    it('shows success message', async () => {
      await resolveDelete(MOCK_GOOD_RESPONSE, 'success!');

      expect(showInfo as jest.Mock).toHaveBeenCalled();
    });

    it('returns true', async () => {
      const result = await resolveDelete(MOCK_GOOD_RESPONSE, 'success!');

      expect(result).toBe(true);
    });
  });
});
