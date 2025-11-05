import "@testing-library/jest-dom";

import type { IFetchStatus } from "breezeTypesStatus";
import { describe, expect, it, vi } from "vitest";
import { responses } from "../../__fixtures__/responses";
import { status } from "../../__fixtures__/status";
import { showError, showInfo } from "../../utils/tooltip";
import { resolveGet } from "./Get";

const MOCK_GOOD_RESPONSE = responses.custom({
	ok: true,
	status: 200,
	json: () =>
		Promise.resolve({
			message: "",
			content: status.basic,
		}),
});

const MOCK_BAD_RESPONSE = responses.custom({
	ok: false,
	status: 400,
	json: () =>
		Promise.resolve({
			message: "some server error",
			content: [],
		}),
});

const MOCK_204_RESPONSE = responses.custom({
	ok: true,
	status: 204,
	json: () =>
		Promise.resolve({
			message: "No content message",
			content: null,
		}),
});

const MOCK_404_RESPONSE = responses.custom({
	ok: false,
	status: 404,
	json: () =>
		Promise.resolve({
			message: "Resource not found",
			content: null,
		}),
});

vi.mock("../../utils/tooltip", () => ({
	showError: vi.fn(() => "some error string"),
	showInfo: vi.fn(() => "some info string"),
}));

describe("resolves Get call", () => {
	afterEach(() => {
		vi.clearAllMocks();
	});
	describe("and resource was fetched", () => {
		it("returns fetched content", async () => {
			const result: IFetchStatus | undefined =
				await resolveGet(MOCK_GOOD_RESPONSE);

			expect(result).toBe(status.basic);
		});
	});

	describe("and resource returns 404", () => {
		it("returns null", async () => {
			const result = await resolveGet(MOCK_404_RESPONSE);

			expect(result).toBeNull();
		});
	});
});
