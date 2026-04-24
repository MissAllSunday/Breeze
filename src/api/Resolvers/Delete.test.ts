import "@testing-library/jest-dom";

import { afterEach, describe, expect, it, vi } from "vitest";
import { responses } from "../../__fixtures__/responses";
import { showError, showInfo } from "../../utils/tooltip";
import { updateCsrfToken } from "../Base";
import { resolveDelete } from "./Delete";

const MOCK_BAD_RESPONSE = responses.custom({
	ok: false,
	status: 400,
	json: () => Promise.resolve({ message: "some server error", token: { var: "bad_var", value: "bad_value" } }),
});

const MOCK_GOOD_RESPONSE = responses.custom({
	ok: true,
	status: 200,
	json: () => Promise.resolve({ message: "some server error", token: { var: "good_var", value: "good_value" } }),
});

const MOCK_EMPTY_200_RESPONSE = responses.custom({
	ok: true,
	status: 200,
	json: () => Promise.reject(new SyntaxError("Unexpected end of JSON input")),
});

vi.mock("../../utils/tooltip", () => ({
	showError: vi.fn(() => "some error string"),
	showInfo: vi.fn(() => "some success string"),
}));

vi.mock("../Base", () => ({
	updateCsrfToken: vi.fn(),
}));

describe("resolves Deleting call", () => {
	afterEach(() => {
		vi.clearAllMocks();
	});

	describe("and resource was not deleted", () => {
		it("shows error message", async () => {
			await resolveDelete(MOCK_BAD_RESPONSE, "success!");

			expect(showError as jest.Mock).toHaveBeenCalled();
		});
		it("returns false", async () => {
			const result = await resolveDelete(MOCK_BAD_RESPONSE, "success!");

			expect(result).toBe(false);
		});
		it("updates csrf token", async () => {
			await resolveDelete(MOCK_BAD_RESPONSE, "success!");

			expect(updateCsrfToken as jest.Mock).toHaveBeenCalledWith({ var: "bad_var", value: "bad_value" });
		});
	});
	describe("and resource was deleted", () => {
		it("shows success message", async () => {
			await resolveDelete(MOCK_GOOD_RESPONSE, "success!");

			expect(showInfo as jest.Mock).toHaveBeenCalled();
		});

		it("returns true", async () => {
			const result = await resolveDelete(MOCK_GOOD_RESPONSE, "success!");

			expect(result).toBe(true);
		});
		it("updates csrf token", async () => {
			await resolveDelete(MOCK_GOOD_RESPONSE, "success!");

			expect(updateCsrfToken as jest.Mock).toHaveBeenCalledWith({ var: "good_var", value: "good_value" });
		});
		it("returns true even with an empty 200 body", async () => {
			const result = await resolveDelete(MOCK_EMPTY_200_RESPONSE, "success!");

			expect(result).toBe(true);
			expect(showInfo as jest.Mock).toHaveBeenCalled();
			expect(updateCsrfToken as jest.Mock).not.toHaveBeenCalled();
		});
	});
});
