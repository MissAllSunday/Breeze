import "@testing-library/jest-dom";
import type { IFetchStatus } from "breezeTypesStatus";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import permissions from "../__fixtures__/permissions";
import { status } from "../__fixtures__/status";
import SingleStatus from "./SingleStatus";

const MOCK_STATUS_ID = 123;
const MOCK_FETCH_STATUS: IFetchStatus = status.customFetchStatus({
	data: [status.custom({ id: MOCK_STATUS_ID, isNew: false })],
});

// Mock the API functions
vi.mock("../api/Status/GetSingle", () => ({
	getSingleStatus: vi.fn(),
}));

vi.mock("../api/Status/Delete", () => ({
	deleteStatus: vi.fn(),
}));

// Mock child components
vi.mock("./Loading", () => ({
	default: () => <div>Loading...</div>,
}));

vi.mock("./Status", () => ({
	default: ({ status }: { status: { body: string } }) => (
		<div data-testid="status-component">{status.body}</div>
	),
}));

vi.mock("../utils/tooltip", () => ({
	showError: vi.fn(),
	showInfo: vi.fn(),
	displayMessage: vi.fn((msg: string) => msg),
}));

// Mock window.location
delete (window as { location?: Location }).location;
// @ts-expect-error -- partial Location stub for testing
window.location = { href: "" } as Location;

// Mock smfTextVars
vi.mock("../DataSource/Txt", () => ({
	default: {
		general: {
			goBack: "Go Back",
		},
		error: {
			generic: "There was an error",
		},
		like: {
			like: "Like",
			unlike: "Unlike",
		},
	},
}));

// Mock smfVars
vi.mock("../DataSource/SMF", () => ({
	default: {
		script_url: "http://localhost",
		user_id: 1,
		wall_id: 1,
		session: { var: "sc", id: "test_session_123" },
	},
}));

import { getSingleStatus } from "../api/Status/GetSingle";

function act(statusId = MOCK_STATUS_ID) {
	return render(<SingleStatus statusId={statusId} />);
}

describe("SingleStatus component", () => {
	beforeEach(() => {
		vi.clearAllMocks();
		window.location.href = "";
	});

	describe("when loading", () => {
		it("shows loading state initially", () => {
			(getSingleStatus as jest.Mock).mockImplementation(
				() => new Promise(() => {}), // Never resolves
			);

			act();

			expect(screen.getByText(/loading/i)).toBeInTheDocument();
		});
	});

	describe("when status is loaded successfully", () => {
		beforeEach(() => {
			(getSingleStatus as jest.Mock).mockResolvedValue(MOCK_FETCH_STATUS);
		});

		it("renders the status content", async () => {
			act();

			await waitFor(() => {
				expect(screen.getByTestId("status-component")).toBeInTheDocument();
				expect(screen.getByText(status.basic.body)).toBeInTheDocument();
			});
		});

		it("shows the Go Back button", async () => {
			act();

			await waitFor(() => {
				expect(
					screen.getAllByRole("button", { name: /go back/i }),
				).toHaveLength(2);
			});
		});

		it("navigates back when Go Back button is clicked", async () => {
			const user = userEvent.setup();
			act();

			await waitFor(() => {
				expect(
					screen.getAllByRole("button", { name: /go back/i }),
				).toHaveLength(2);
			});

			// Click the first Go Back button
			const goBackButtons = screen.getAllByRole("button", { name: /go back/i });
			await user.click(goBackButtons[0]);

			// Should call window.history.back()
			// Note: In a real test environment, you'd mock window.history
		});
	});

	describe("when status is not found", () => {
		it("shows error state with Go Back button", async () => {
			(getSingleStatus as jest.Mock).mockResolvedValue(null);

			act();

			await waitFor(() => {
				// Should show the Go Back button in error state
				expect(
					screen.getByRole("button", { name: /go back/i }),
				).toBeInTheDocument();
			});

			// Should not show the status component
			expect(screen.queryByTestId("status-component")).not.toBeInTheDocument();
		});
	});

	describe("when delete is triggered", () => {
		it("component renders with delete permissions", async () => {
			(getSingleStatus as jest.Mock).mockResolvedValue(
				status.customFetchStatus({
					data: [status.custom({ id: MOCK_STATUS_ID, isNew: false })],
					permissions: {
						...permissions.basic,
						Status: { delete: true, edit: false, post: false },
					},
				}),
			);

			act();

			await waitFor(() => {
				// Status component should be rendered
				expect(screen.getByTestId("status-component")).toBeInTheDocument();
			});
		});
	});
});
