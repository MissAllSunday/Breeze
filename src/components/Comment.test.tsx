import "@testing-library/jest-dom";
import type { CommentType } from "breezeTypesComments";
import type { PermissionsContextType } from "breezeTypesPermissions";
import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { comments } from "../__fixtures__/comments";
import permissions from "../__fixtures__/permissions";
import { PermissionsContext } from "../context/PermissionsContext";
import { commentActionRegistry } from "./actions/actionRegistry";
import CommentDeleteAction from "./actions/comment/DeleteAction";
import CommentLikeAction from "./actions/comment/LikeAction";
import Comment from "./Comment";

// Prevent real HTTP calls from action descriptors
vi.mock("../api/Like/Post", () => ({ postLike: vi.fn().mockResolvedValue({}) }));

function act(
	overwritePermissions?: Partial<PermissionsContextType>,
	custom?: Partial<CommentType>,
	onRemoved = true,
) {
	const actOnRemoved = () => onRemoved;
	const customComment = { ...comments.basic, ...custom };

	return render(
		<PermissionsContext.Provider
			value={
				overwritePermissions
					? permissions.custom(overwritePermissions)
					: permissions.basic
			}
		>
			<Comment comment={customComment} removeComment={actOnRemoved} />
		</PermissionsContext.Provider>,
	);
}

beforeAll(() => {
	window.confirm = vi.fn(() => true);
	commentActionRegistry.register(CommentLikeAction, CommentDeleteAction);
});

describe("Rendering Comment component", () => {
	it("renders with default comment data", () => {
		const { container } = act();

		expect(container.firstChild).toHaveClass("comment");
	});
	it("renders the comment content", () => {
		const { container } = act();

		expect(container.getElementsByClassName("content")[0].textContent).toEqual(
			comments.basic.body,
		);
	});
});

describe("Deleting a comment", () => {
	it("does not show delete button when user does not have permissions", () => {
		act();
		expect(screen.queryByTestId("deleteComment")).not.toBeInTheDocument();
	});
	it("show delete button when user does have permissions", () => {
		act({ Comments: { delete: true, edit: true, post: true } });
		expect(screen.getByTestId("deleteComment")).toBeInTheDocument();
	});
});
