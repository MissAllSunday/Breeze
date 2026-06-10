import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import { likesInfo } from "../../../__fixtures__/likesInfo";
import permissions from "../../../__fixtures__/permissions";
import { status } from "../../../__fixtures__/status";
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
	status: status.basic,
	permissions: permissions.basic,
	closePanel: vi.fn(),
	createComment: vi.fn().mockReturnValue(true),
	removeStatus: vi.fn(),
	updateLikesInfo: vi.fn(),
	...overrides,
});

describe("LikeAction descriptor", () => {
	it("has id 'like'", () => {
		expect(LikeAction.id).toBe("like");
	});

	it("has order 10", () => {
		expect(LikeAction.order).toBe(10);
	});

	it("resolves icon to 'like' when not yet liked", () => {
		const ctx = makeCtx();
		const icon = typeof LikeAction.icon === "function" ? LikeAction.icon(ctx) : LikeAction.icon;
		expect(icon).toBe("like");
	});

	it("resolves icon to 'unlike' when already liked", () => {
		const ctx = makeCtx({ status: { ...status.basic, likesInfo: likesInfo.custom({ alreadyLiked: true }) } });
		const icon = typeof LikeAction.icon === "function" ? LikeAction.icon(ctx) : LikeAction.icon;
		expect(icon).toBe("unlike");
	});

	it("resolves label to 'Like' when not yet liked", () => {
		const ctx = makeCtx();
		const label = typeof LikeAction.label === "function" ? LikeAction.label(ctx) : LikeAction.label;
		expect(label).toBe("Like");
	});

	it("resolves label to 'Unlike' when already liked", () => {
		const ctx = makeCtx({ status: { ...status.basic, likesInfo: likesInfo.custom({ alreadyLiked: true }) } });
		const label = typeof LikeAction.label === "function" ? LikeAction.label(ctx) : LikeAction.label;
		expect(label).toBe("Unlike");
	});
});

describe("LikeAction.isVisible", () => {
	it("returns true when enableLikes and likesLike are both true", () => {
		const ctx = makeCtx({
			permissions: permissions.custom({
				isEnable: { enableLikes: true },
				Forum: { likesLike: true, adminForum: false, profileView: false },
			}),
		});
		expect(LikeAction.isVisible(ctx)).toBe(true);
	});

	it("returns false when enableLikes is false", () => {
		const ctx = makeCtx({
			permissions: permissions.custom({
				isEnable: { enableLikes: false },
				Forum: { likesLike: true, adminForum: false, profileView: false },
			}),
		});
		expect(LikeAction.isVisible(ctx)).toBe(false);
	});

	it("returns false when likesLike is false", () => {
		const ctx = makeCtx({
			permissions: permissions.custom({
				isEnable: { enableLikes: true },
				Forum: { likesLike: false, adminForum: false, profileView: false },
			}),
		});
		expect(LikeAction.isVisible(ctx)).toBe(false);
	});
});

describe("LikeAction.onClick", () => {
	beforeEach(() => {
		window.confirm = vi.fn().mockReturnValue(true);
	});

	afterEach(() => {
		vi.clearAllMocks();
	});

	it("calls postLike with the status likesInfo", () => {
		const ctx = makeCtx();
		LikeAction.onClick?.(ctx);
		expect(postLike).toHaveBeenCalledWith(status.basic.likesInfo);
	});

	it("calls updateLikesInfo with the postLike result", async () => {
		const updateLikesInfo = vi.fn();
		const ctx = makeCtx({ updateLikesInfo });
		LikeAction.onClick?.(ctx);
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

		LikeAction.onClick?.(makeCtx({ status: { ...status.basic, likesInfo: likesInfo.basic } }));
		expect(postLike).toHaveBeenCalledWith(likesInfo.basic);

		(smfVars as { confirmPost: number }).confirmPost = 0;
	});
});
