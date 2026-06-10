import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import permissions from "../../../__fixtures__/permissions";
import { status } from "../../../__fixtures__/status";
import DeleteAction from "./DeleteAction";

vi.mock("../../../DataSource/SMF", () => ({
	default: { youSure: "Are you sure?" },
}));

vi.mock("../../../DataSource/Txt", () => ({
	default: { actions: { like: "Like", comment: "Comment", delete: "Delete" } },
}));

const makeCtx = (overrides = {}) => ({
	status: status.basic,
	permissions: permissions.basic,
	closePanel: vi.fn(),
	createComment: vi.fn().mockReturnValue(true),
	removeStatus: vi.fn(),
	...overrides,
});

describe("DeleteAction descriptor", () => {
	it("has id 'delete'", () => expect(DeleteAction.id).toBe("delete"));
	it("has order 30", () => expect(DeleteAction.order).toBe(30));
	it("has no Panel", () => expect(DeleteAction.Panel).toBeUndefined());
});

describe("DeleteAction.isVisible", () => {
	it("returns true when Status.delete is true", () => {
		expect(
			DeleteAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						Status: { delete: true, edit: false, post: false },
					}),
				}),
			),
		).toBe(true);
	});

	it("returns false when Status.delete is false", () => {
		expect(
			DeleteAction.isVisible(
				makeCtx({
					permissions: permissions.custom({
						Status: { delete: false, edit: false, post: false },
					}),
				}),
			),
		).toBe(false);
	});
});

describe("DeleteAction.onClick", () => {
	beforeEach(() => {
		window.confirm = vi.fn().mockReturnValue(true);
	});

	afterEach(() => {
		vi.clearAllMocks();
	});

	it("calls window.confirm before acting", () => {
		const ctx = makeCtx();
		DeleteAction.onClick?.(ctx);
		expect(window.confirm).toHaveBeenCalledOnce();
	});

	it("calls removeStatus when user confirms", () => {
		const ctx = makeCtx();
		DeleteAction.onClick?.(ctx);
		expect(ctx.removeStatus).toHaveBeenCalledOnce();
	});

	it("does not call removeStatus when user cancels", () => {
		window.confirm = vi.fn().mockReturnValue(false);
		const ctx = makeCtx();
		DeleteAction.onClick?.(ctx);
		expect(ctx.removeStatus).not.toHaveBeenCalled();
	});
});
