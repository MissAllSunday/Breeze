import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import { comments } from "../../../__fixtures__/comments";
import { likesInfo } from "../../../__fixtures__/likesInfo";
import permissions from "../../../__fixtures__/permissions";
import { postLike } from "../../../api/Like/Post";
import LikeAction from "./LikeAction";

vi.mock("../../../api/Like/Post", () => ({
	postLike: vi.fn().mockResolvedValue({}),
}));

vi.mock("../../../DataSource/SMF", () => ({
	default: { confirmPost: 0, youSure: "Are you sure?" },
}));

vi.mock("../../../DataSource/Txt", () => ({
	default: {
		actions: { like: "Like", comment: "Comment", delete: "Delete" },
		like: { like: "Like", unlike: "Unlike" },
	},
}));

const makeCtx = (overrides = {}) => ({
	comment: comments.basic,
	permissions: permissions.basic,
	closePanel: vi.fn(),
	removeComment: vi.fn(),
	updateLikesInfo: vi.fn(),
	...overrides,
});

describe("comment/LikeAction descriptor", () => {
	it("has id 'like'", () => expect(LikeAction.id).toBe("like"));
	it("has order 10", () => expect(LikeAction.order).toBe(10));
	it("has no Panel", () => expect(LikeAction.Panel).toBeUndefined());

	it("resolves icon to 'like' when not yet liked", () => {
		const ctx = makeCtx();
		const icon = typeof LikeAction.icon === "function" ? LikeAction.icon(ctx) : LikeAction.icon;
		expect(icon).toBe("like");
	});

	it("resolves icon to 'unlike' when already liked", () => {
		const ctx = makeCtx({ comment: { ...comments.basic, likesInfo: likesInfo.custom({ alreadyLiked: true }) } });
		const icon = typeof LikeAction.icon === "function" ? LikeAction.icon(ctx) : LikeAction.icon;
		expect(icon).toBe("unlike");
	});

	it("resolves label to 'Like' when not yet liked", () => {
		const ctx = makeCtx();
		const label = typeof LikeAction.label === "function" ? LikeAction.label(ctx) : LikeAction.label;
		expect(label).toBe("Like");
	});

	it("resolves label to 'Unlike' when already liked", () => {
		const ctx = makeCtx({ comment: { ...comments.basic, likesInfo: likesInfo.custom({ alreadyLiked: true }) } });
		const label = typeof LikeAction.label === "function" ? LikeAction.label(ctx) : LikeAction.label;
		expect(label).toBe("Unlike");
	});
});

describe("comment/LikeAction.isVisible", () => {
	it("returns true when enableLikes and likesLike are both true", () => {
		expect(
			LikeAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						isEnable: { enableLikes: true },
						Forum: { likesLike: true, adminForum: false, profileView: false },
					}),
				}),
			),
		).toBe(true);
	});

	it("returns false when enableLikes is false", () => {
		expect(
			LikeAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						isEnable: { enableLikes: false },
						Forum: { likesLike: true, adminForum: false, profileView: false },
					}),
				}),
			),
		).toBe(false);
	});

	it("returns false when likesLike is false", () => {
		expect(
			LikeAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						isEnable: { enableLikes: true },
						Forum: { likesLike: false, adminForum: false, profileView: false },
					}),
				}),
			),
		).toBe(false);
	});
});

describe("comment/LikeAction.onClick", () => {
	beforeEach(() => {
		window.confirm = vi.fn().mockReturnValue(true);
	});

	afterEach(() => {
		vi.clearAllMocks();
	});

	it("calls postLike with the comment likesInfo", () => {
		LikeAction.onClick?.(makeCtx());
		expect(postLike).toHaveBeenCalledWith(comments.basic.likesInfo);
	});

	it("calls updateLikesInfo with the postLike result", async () => {
		const updateLikesInfo = vi.fn();
		LikeAction.onClick?.(makeCtx({ updateLikesInfo }));
		await vi.mocked(postLike).mock.results[0].value;
		expect(updateLikesInfo).toHaveBeenCalledWith({});
	});

	it("does not call postLike when confirmPost is on and user cancels", async () => {
		const { default: smfVars } = await import("../../../DataSource/SMF");
		(smfVars as { confirmPost: number }).confirmPost = 1;
		window.confirm = vi.fn().mockReturnValue(false);

		LikeAction.onClick?.(makeCtx());
		expect(postLike).not.toHaveBeenCalled();

		(smfVars as { confirmPost: number }).confirmPost = 0;
	});

	it("calls postLike when confirmPost is on and user confirms", async () => {
		const { default: smfVars } = await import("../../../DataSource/SMF");
		(smfVars as { confirmPost: number }).confirmPost = 1;
		window.confirm = vi.fn().mockReturnValue(true);

		LikeAction.onClick?.(makeCtx({ comment: { ...comments.basic, likesInfo: likesInfo.basic } }));
		expect(postLike).toHaveBeenCalledWith(likesInfo.basic);

		(smfVars as { confirmPost: number }).confirmPost = 0;
	});
});
