import "@testing-library/jest-dom";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent, { type UserEvent } from "@testing-library/user-event";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import smfVars from "../DataSource/SMF";
import smfTextVars from "../DataSource/Txt";
import { showError } from "../utils/tooltip";
import Editor from "./Editor";

// Mock external dependencies
vi.mock("../DataSource/SMF", () => ({
	default: {
		smfEditorHandler: {
			create: vi.fn(),
			instance: vi.fn(() => ({
				createPermanentDropDown: vi.fn(),
				toggleSourceMode: vi.fn(),
				val: vi.fn(() => "mocked editor content"),
			})),
		},
		editorOptions: {
			emoticonsEnabled: true,
		},
		editorIsRich: true,
		youSure: "Are you sure?",
		confirmPost: 1,
	},
}));
vi.mock("../DataSource/Txt", () => ({
	default: {
		error: {
			errorEmpty: "Content cannot be empty.",
		},
		general: {
			send: "Send",
		},
	},
}));
vi.mock("../utils/tooltip", () => ({
	showError: vi.fn(),
}));

const mockSaveContent = vi.fn(() => true);
const originalConfirm = window.confirm;

beforeEach(() => {
	window.confirm = vi.fn(() => true);
	vi.clearAllMocks();
});

afterEach(() => {
	window.confirm = originalConfirm;
});

describe("Editor component", () => {
	it("renders the textarea and send button", () => {
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		expect(screen.getByTestId("content")).toBeInTheDocument();
		expect(screen.getByTestId("send")).toBeInTheDocument();
	});

	it("updates content when typing in the textarea", async () => {
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const textarea = screen.getByTestId("content");

		await user.type(textarea, "Test content");
		expect(textarea).toHaveValue("Test content");
	});

	it("calls saveContent with textarea content when not full editor", async () => {
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const textarea = screen.getByTestId("content");
		const sendButton = screen.getByTestId("send");

		await user.type(textarea, "Test content");
		await user.click(sendButton);

		await waitFor(() => {
			expect(mockSaveContent).toHaveBeenCalledWith("Test content");
			expect(textarea).toHaveValue(""); // Content should be cleared
		});
	});

	it("calls saveContent with mocked editor content when full editor", async () => {
		render(<Editor saveContent={mockSaveContent} isFull={true} />);
		const user: UserEvent = userEvent.setup();
		const sendButton = screen.getByTestId("send");

		// Mock the val() method of the editor instance
		(smfVars.smfEditorHandler.instance as vi.Mock).mockReturnValue({
			val: vi.fn(() => "mocked full editor content"),
			createPermanentDropDown: vi.fn(),
			toggleSourceMode: vi.fn(),
		});

		await user.click(sendButton);

		await waitFor(() => {
			expect(mockSaveContent).toHaveBeenCalledWith(
				"mocked full editor content",
			);
			// For full editor, the val() method is called to clear the content
			expect(smfVars.smfEditorHandler.instance().val).toHaveBeenCalledWith("");
		});
	});

	it("shows error if content is empty and not confirmed", async () => {
		window.confirm = vi.fn(() => false); // User cancels confirmation
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const sendButton = screen.getByTestId("send");

		await user.click(sendButton);

		await waitFor(() => {
			expect(window.confirm).toHaveBeenCalledWith(smfVars.youSure);
			expect(mockSaveContent).not.toHaveBeenCalled();
			expect(showError).not.toHaveBeenCalled(); // No error if user cancels
		});
	});

	it("shows error if content is empty and confirmed", async () => {
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const sendButton = screen.getByTestId("send");

		await user.click(sendButton); // Click without typing content

		await waitFor(() => {
			expect(window.confirm).toHaveBeenCalledWith(smfVars.youSure);
			expect(mockSaveContent).not.toHaveBeenCalled();
			expect(showError).toHaveBeenCalledWith(smfTextVars.error.errorEmpty);
		});
	});

	it("does not clear content if saveContent returns false", async () => {
		mockSaveContent.mockReturnValueOnce(false); // Simulate save failure
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const textarea = screen.getByTestId("content");
		const sendButton = screen.getByTestId("send");

		await user.type(textarea, "Test content");
		await user.click(sendButton);

		await waitFor(() => {
			expect(mockSaveContent).toHaveBeenCalledWith("Test content");
			expect(textarea).toHaveValue("Test content"); // Content should not be cleared
		});
	});

	it("initializes SMF editor when isFull is true", () => {
		render(<Editor saveContent={mockSaveContent} isFull={true} />);
		expect(smfVars.smfEditorHandler.create).toHaveBeenCalled();
		expect(
			smfVars.smfEditorHandler.instance().createPermanentDropDown,
		).toHaveBeenCalled();
		expect(
			smfVars.smfEditorHandler.instance().toggleSourceMode,
		).not.toHaveBeenCalled(); // editorIsRich is true
	});

	it("does not initialize SMF editor when isFull is false", () => {
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		expect(smfVars.smfEditorHandler.create).not.toHaveBeenCalled();
	});

	it("skips confirmation dialog when confirmPost is 0", async () => {
		smfVars.confirmPost = 0;
		render(<Editor saveContent={mockSaveContent} isFull={false} />);
		const user: UserEvent = userEvent.setup();
		const textarea = screen.getByTestId("content");
		const sendButton = screen.getByTestId("send");

		await user.type(textarea, "No confirm needed");
		await user.click(sendButton);

		await waitFor(() => {
			expect(window.confirm).not.toHaveBeenCalled();
			expect(mockSaveContent).toHaveBeenCalledWith("No confirm needed");
		});

		smfVars.confirmPost = 1;
	});
});
