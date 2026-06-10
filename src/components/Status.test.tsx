import "@testing-library/jest-dom";
import type { PermissionsContextType } from "breezeTypesPermissions";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent, { type UserEvent } from "@testing-library/user-event";
import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from "vitest";

import { comments } from "../__fixtures__/comments";
import permissions from "../__fixtures__/permissions";
import { status } from "../__fixtures__/status";
import { deleteComment } from "../api/Comment/Delete";
import { postComment } from "../api/Comment/Post";
import { PermissionsContext } from "../context/PermissionsContext";
import smfVars from "../DataSource/SMF";
import { commentActionRegistry, statusActionRegistry } from "./actions/actionRegistry";
import CommentDeleteAction from "./actions/comment/DeleteAction";
import CommentLikeAction from "./actions/comment/LikeAction";
import CommentAction from "./actions/status/CommentAction";
import DeleteAction from "./actions/status/DeleteAction";
import LikeAction from "./actions/status/LikeAction";
import Status from "./Status";

// Prevent real HTTP calls from action descriptors
vi.mock("../api/Like/Post", () => ({ postLike: vi.fn().mockResolvedValue({}) }));

const mockRemoveStatus = vi.fn(() => true);

// Mock the API calls
vi.mock("../api/Comment/Post");
vi.mock("../api/Comment/Delete");

// Populate both shared registries once before any test runs
beforeAll(() => {
	statusActionRegistry.register(LikeAction, CommentAction, DeleteAction);
	commentActionRegistry.register(CommentLikeAction, CommentDeleteAction);
});

const originalConfirm = window.confirm; // Store original function

beforeEach(() => {
	window.confirm = vi.fn(() => true);
	Element.prototype.scrollIntoView = vi.fn();
});

afterEach(() => {
	window.confirm = originalConfirm; // Restore original function
	vi.clearAllMocks(); // Clear mock calls
});

function act(overwritePermissions?: Partial<PermissionsContextType>) {
	const customProps = {};
	return render(
		<PermissionsContext.Provider
			value={
				overwritePermissions
					? permissions.custom(overwritePermissions)
					: permissions.basic
			}
		>
			<Status
				status={status.basic}
				removeStatus={mockRemoveStatus}
				{...customProps}
			/>
		</PermissionsContext.Provider>,
	);
}

describe("Status component", () => {
	afterEach(() => {
		vi.clearAllMocks();
		vi.resetAllMocks();
	});

	it("renders the status content", () => {
		const { container } = act();

		expect(container.querySelector(".content")).toHaveProperty(
			"innerHTML",
			status.basic.body,
		);
	});

	it("shows delete button when user has permission", () => {
		act({ Status: { delete: true, edit: true, post: false } });

		const deleteButton = screen.getByTestId("deleteStatus");
		expect(deleteButton).toBeInTheDocument();
	});

	it("hides delete button when user lacks permission", () => {
		act();

		const deleteButton = screen.queryByTestId("deleteStatus");
		expect(deleteButton).not.toBeInTheDocument();
	});

	it("calls removeStatus when delete button is clicked and confirmed", async () => {
		act({ Status: { delete: true, edit: true, post: true } });

		const user: UserEvent = userEvent.setup();
		const deleteButton = screen.getByTestId("deleteStatus");

		await waitFor(() => {
			user.click(deleteButton);

			expect(window.confirm).toHaveBeenCalledWith(smfVars.youSure);
			expect(mockRemoveStatus).toHaveBeenCalled();
		});
	});

	it("does not call removeStatus when delete is canceled", async () => {
		window.confirm = vi.fn().mockImplementation(() => false);
		act({ Status: { delete: true, edit: true, post: true } });

		const deleteButton = screen.getByTestId("deleteStatus");
		await userEvent.click(deleteButton);

		expect(window.confirm).toHaveBeenCalledWith(smfVars.youSure);
		expect(mockRemoveStatus).not.toHaveBeenCalled();
	});

	it("shows comment form when user has permission to post comments", async () => {
		act({ Comments: { delete: true, edit: true, post: true } });

		// Editor lives inside the CommentAction panel — open it first
		await userEvent.click(screen.getByRole("button", { name: /comment/i }));

		const commentForm = screen.getByTestId("content");
		expect(commentForm).toBeInTheDocument();
	});

	it("hides comment form when user lacks permission to post comments", () => {
		act();

		expect(screen.queryByTestId("content")).not.toBeInTheDocument();
	});

	it("posts a new comment successfully", async () => {
		(postComment as jest.Mock).mockResolvedValue([
			comments.custom({ id: 667 }),
		]);
		act({ Comments: { delete: true, edit: true, post: true } });

		// Editor is in the CommentAction panel — open it first
		await userEvent.click(screen.getByRole("button", { name: /comment/i }));

		const editor = screen.getByTestId("content");
		const sendButton = screen.getByTestId("send");
		await userEvent.type(editor, "New comment");
		await userEvent.click(sendButton);

		await waitFor(() => {
			expect(postComment).toHaveBeenCalledWith({
				status_id: status.basic.id,
				body: "New comment",
				mention_ids: [],
			});
		});
	});

	it("deletes a comment successfully", async () => {
		(deleteComment as jest.Mock).mockResolvedValue(true);

		act({ Comments: { delete: true, edit: true, post: true } });

		// Find and click the delete button on a comment
		const commentDeleteButton = screen.getByTestId("deleteComment");
		await userEvent.click(commentDeleteButton);

		await waitFor(() => {
			expect(deleteComment).toHaveBeenCalledWith(status.basic.comments[0].id);
		});
	});

	it("shows loading indicator when performing async operations", async () => {
		(postComment as jest.Mock).mockImplementation(() => {
			return new Promise((resolve) => {
				setTimeout(() => resolve([]), 100);
			});
		});

		act({ Comments: { delete: true, edit: true, post: true } });

		// Editor is in the CommentAction panel — open it first
		await userEvent.click(screen.getByRole("button", { name: /comment/i }));

		// Trigger an async operation
		const editor = screen.getByTestId("content");
		const sendButton = screen.getByTestId("send");
		await userEvent.type(editor, "New comment");
		await userEvent.click(sendButton);

		// Wait for operation to complete
		await waitFor(() => {
			expect(screen.queryByTestId("loading")).not.toBeInTheDocument();
		});
	});
});
