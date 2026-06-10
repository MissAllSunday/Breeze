import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import { comments } from "../../../__fixtures__/comments";
import permissions from "../../../__fixtures__/permissions";
import DeleteAction from "./DeleteAction";

vi.mock("../../../DataSource/SMF", () => ({
	default: { youSure: "Are you sure?" },
}));

vi.mock("../../../DataSource/Txt", () => ({
	default: { actions: { like: "Like", comment: "Comment", delete: "Delete" } },
}));

const makeCtx = (overrides = {}) => ({
	comment: comments.basic,
	permissions: permissions.basic,
	closePanel: vi.fn(),
	removeComment: vi.fn(),
	...overrides,
});

describe("comment/DeleteAction descriptor", () => {
	it("has id 'delete'", () => expect(DeleteAction.id).toBe("delete"));
	it("has order 20", () => expect(DeleteAction.order).toBe(20));
	it("has no Panel", () => expect(DeleteAction.Panel).toBeUndefined());
});

describe("comment/DeleteAction.isVisible", () => {
	it("returns true when Comments.delete is true", () => {
		expect(
			DeleteAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						Comments: { delete: true, edit: false, post: false },
					}),
				}),
			),
		).toBe(true);
	});

	it("returns false when Comments.delete is false", () => {
		expect(
			DeleteAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						Comments: { delete: false, edit: false, post: false },
					}),
				}),
			),
		).toBe(false);
	});
});

describe("comment/DeleteAction.onClick", () => {
	beforeEach(() => {
		window.confirm = vi.fn().mockReturnValue(true);
	});

	afterEach(() => {
		vi.clearAllMocks();
	});

	it("calls window.confirm before acting", () => {
		DeleteAction.onClick?.(makeCtx());
		expect(window.confirm).toHaveBeenCalledOnce();
	});

	it("calls removeComment when user confirms", () => {
		const ctx = makeCtx();
		DeleteAction.onClick?.(ctx);
		expect(ctx.removeComment).toHaveBeenCalledOnce();
	});

	it("does not call removeComment when user cancels", () => {
		window.confirm = vi.fn().mockReturnValue(false);
		const ctx = makeCtx();
		DeleteAction.onClick?.(ctx);
		expect(ctx.removeComment).not.toHaveBeenCalled();
	});
});
