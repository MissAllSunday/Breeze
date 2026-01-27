import "@testing-library/jest-dom";

import { likesInfo } from "../../__fixtures__/likesInfo";
import { showError } from "../../utils/tooltip";
import { baseConfig, baseUrl } from "../Base";
import { resolvePost } from "../Resolvers/Post";
import { postLike } from "./Post";

const MOCK_LIKE_INFO_ITEM = likesInfo.basic;

vi.mock("../Base", () => ({
	baseUrl: vi.fn(() => "some url"),
	baseConfig: vi.fn(() => "some config"),
}));

vi.mock("../../utils/tooltip", () => ({
	showError: vi.fn(() => "some error"),
}));

vi.mock("../Resolvers/Post", () => ({
	resolvePost: vi.fn(),
}));

describe("posting a like", () => {
	it("calls base url", async () => {
		vi.spyOn(global, "fetch").mockResolvedValueOnce(
			Promise.resolve(new Response()),
		);
		await postLike(MOCK_LIKE_INFO_ITEM);

		expect(baseUrl as jest.Mock).toHaveBeenCalled();
	});

	it("calls base config", async () => {
		vi.spyOn(global, "fetch").mockResolvedValueOnce(
			Promise.resolve(new Response()),
		);
		await postLike(MOCK_LIKE_INFO_ITEM);

		expect(baseConfig as jest.Mock).toHaveBeenCalled();
	});

	describe("and sent correct params", () => {
		it("calls resolver", async () => {
			vi.spyOn(global, "fetch").mockResolvedValueOnce(
				Promise.resolve(new Response()),
			);
			await postLike(MOCK_LIKE_INFO_ITEM);

			expect(resolvePost as jest.Mock).toHaveBeenCalled();
		});
	});
	describe("and sent wrong params", () => {
		it("shows error message", async () => {
			vi.spyOn(global, "fetch").mockResolvedValueOnce(
				Promise.reject(new Error("something")),
			);
			await expect(postLike(MOCK_LIKE_INFO_ITEM)).rejects.toThrow("something");
			expect(showError as jest.Mock).toHaveBeenCalled();
		});
	});
});
