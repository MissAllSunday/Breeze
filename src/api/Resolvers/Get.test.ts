import '@testing-library/jest-dom';

import type { IFetchStatus } from 'breezeTypesStatus';
import { describe, expect, it, vi } from 'vitest';
import { responses } from '../../__fixtures__/responses';
import { status } from '../../__fixtures__/status';
import { showError } from '../../utils/tooltip';
import { resolveGet } from './Get';

const MOCK_GOOD_RESPONSE = responses.custom({
  ok: true,
  status: 200,
  json: () => Promise.resolve({
    message: '',
    content: status.basic,
  }),
});

const MOCK_BAD_RESPONSE = responses.custom({
  ok: false,
  status: 400,
  json: () => Promise.resolve({
    message: 'some server error',
    content: [],
  }),
});

vi.mock('../../utils/tooltip', () => ({
  showError: vi.fn(() => 'some error string'),
}));

describe('resolves Get call', () => {
  describe('and call has a message', () => {
    it('shows error message', async () => {
      await resolveGet(MOCK_BAD_RESPONSE);

      expect(showError as jest.Mock).toHaveBeenCalled();
    });
  });
  describe('and resource was fetched', () => {

    it('returns fetched content', async () => {
      const result: IFetchStatus | undefined = await resolveGet(MOCK_GOOD_RESPONSE);

      expect(result).toBe(status.basic);
    });
  });
});
