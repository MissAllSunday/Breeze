import "@testing-library/jest-dom";

import { beforeEach, describe, expect, it, vi } from "vitest";
import { status } from "../../__fixtures__/status";
import { showError } from "../../utils/tooltip";
import { baseUrl } from "../Base";
import { resolveGet } from "../Resolvers/Get";
import { getSingleStatus } from "./GetSingle";

const MOCK_STATUS_ID = 123;

vi.mock("../../utils/tooltip", () => ({
	showError: vi.fn(() => "some error string"),
	showInfo: vi.fn(() => "some info string"),
}));

vi.mock("../Base", () => ({
	baseUrl: vi.fn(() => "http://localhost?action=breezeStatus&sa=single&id=123"),
}));

vi.mock("../Resolvers/Get", () => ({
	resolveGet: vi.fn(),
}));

describe("getSingleStatus API function", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("calls baseUrl with correct parameters", async () => {
		vi.spyOn(global, "fetch").mockResolvedValueOnce(new Response());
		await getSingleStatus(MOCK_STATUS_ID);

		expect(baseUrl as jest.Mock).toHaveBeenCalledWith(
			"breezeStatus",
			"single",
			[{ id: MOCK_STATUS_ID }],
		);
	});

	describe("when status is fetched successfully", () => {
		it("calls resolveGet with the response", async () => {
			const mockResponse = new Response();
			vi.spyOn(global, "fetch").mockResolvedValueOnce(mockResponse);
			(resolveGet as jest.Mock).mockResolvedValue(status.fetchStatus);

			const result = await getSingleStatus(MOCK_STATUS_ID);

			expect(resolveGet as jest.Mock).toHaveBeenCalledWith(mockResponse);
			expect(result).toEqual(status.fetchStatus);
		});
	});

	describe("when fetch throws an error", () => {
		it("shows error message", async () => {
			vi.spyOn(global, "fetch").mockRejectedValueOnce(
				new Error("Network error"),
			);

			await getSingleStatus(MOCK_STATUS_ID);

			expect(showError as jest.Mock).toHaveBeenCalled();
		});
	});
});
