import "@testing-library/jest-dom";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

import permissions from "../../../__fixtures__/permissions";
import { status } from "../../../__fixtures__/status";
import CommentAction from "./CommentAction";

// Lean mocks — only the bits CommentPanel actually uses
vi.mock("../../Editor", () => ({
	default: ({ saveContent }: { saveContent: (c: string) => boolean }) => (
		<button
			type="button"
			data-testid="mock-editor-send"
			onClick={() => saveContent("test comment")}
		>
			Send
		</button>
	),
}));

vi.mock("../../user/Avatar", () => ({
	default: () => <div data-testid="mock-avatar" />,
}));

vi.mock("../../../DataSource/SMF", () => ({
	default: { currentUserAvatar: "/avatar.png", confirmPost: 0, youSure: "" },
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

const Panel = CommentAction.Panel;
if (!Panel) throw new Error("CommentAction.Panel must be defined");

describe("CommentAction descriptor", () => {
	it("has id 'comment'", () => expect(CommentAction.id).toBe("comment"));
	it("has order 20", () => expect(CommentAction.order).toBe(20));
	it("has a Panel component", () => expect(CommentAction.Panel).toBeDefined());
});

describe("CommentAction.isVisible", () => {
	it("returns true when Comments.post is true", () => {
		expect(
			CommentAction.isVisible(
				makeCtx({ permissions: permissions.custom({ Comments: { post: true, delete: false, edit: false } }) }),
			),
		).toBe(true);
	});

	it("returns false when Comments.post is false", () => {
		expect(
			CommentAction.isVisible(
				makeCtx({ permissions: permissions.custom({ Comments: { post: false, delete: false, edit: false } }) }),
			),
		).toBe(false);
	});
});

describe("CommentAction.Panel", () => {
	it("renders the avatar", () => {
		const ctx = makeCtx();
		render(<Panel {...ctx} />);
		expect(screen.getByTestId("mock-avatar")).toBeInTheDocument();
	});

	it("renders the editor", () => {
		const ctx = makeCtx();
		render(<Panel {...ctx} />);
		expect(screen.getByTestId("mock-editor-send")).toBeInTheDocument();
	});

	it("calls createComment when editor submits", async () => {
		const ctx = makeCtx();
		render(<Panel {...ctx} />);
		await userEvent.click(screen.getByTestId("mock-editor-send"));
		// The Panel forwards (content, mentionIds) — mentionIds is undefined here
		expect(ctx.createComment).toHaveBeenCalledWith("test comment", undefined);
	});

	it("calls closePanel after successful createComment", async () => {
		const ctx = makeCtx({ createComment: vi.fn().mockReturnValue(true) });
		render(<Panel {...ctx} />);
		await userEvent.click(screen.getByTestId("mock-editor-send"));
		expect(ctx.closePanel).toHaveBeenCalledOnce();
	});

	it("does not call closePanel when createComment returns false", async () => {
		const ctx = makeCtx({ createComment: vi.fn().mockReturnValue(false) });
		render(<Panel {...ctx} />);
		await userEvent.click(screen.getByTestId("mock-editor-send"));
		expect(ctx.closePanel).not.toHaveBeenCalled();
	});
});
