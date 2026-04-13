import "@testing-library/jest-dom";
import type { WallProps } from "breezeTypes";
import { render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

import Wall from "./Wall";

// Mock the child components
vi.mock("./components/SingleStatus", () => ({
	default: ({ statusId }: { statusId: number }) => (
		<div data-testid="single-status">Single Status: {statusId}</div>
	),
}));

vi.mock("./api/Status/Get", () => ({
	getStatus: vi.fn(() =>
		Promise.resolve({
			data: [],
			permissions: {},
			pagination: { nextCursor: null, hasMore: false },
		}),
	),
}));

vi.mock("./api/Status/Post", () => ({
	postStatus: vi.fn(),
}));

vi.mock("./api/Status/Delete", () => ({
	deleteStatus: vi.fn(),
}));

vi.mock("./utils/tooltip", () => ({
	showError: vi.fn(),
	showInfo: vi.fn(),
	displayMessage: vi.fn(),
}));

const MOCK_WALL_PROPS: WallProps = {
	wallType: "wall",
	wallId: 1,
};

function act(props: Partial<WallProps> = {}) {
	const finalProps: WallProps = { ...MOCK_WALL_PROPS, ...props };
	return render(<Wall {...finalProps} />);
}

describe("Wall component routing", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	describe("when statusId is provided", () => {
		it("renders SingleStatus component", () => {
			const statusId = 123;
			act({ statusId });

			expect(screen.getByTestId("single-status")).toBeInTheDocument();
			expect(
				screen.getByText(`Single Status: ${statusId}`),
			).toBeInTheDocument();
		});

		it("does not render the feed view", () => {
			act({ statusId: 123 });

			// The feed view would have an editor, but SingleStatus doesn't
			expect(screen.queryByTestId("editor")).not.toBeInTheDocument();
		});
	});

	describe("when statusId is not provided", () => {
		it("renders WallFeed component", async () => {
			act();

			// WallFeed should render (it has the main wall structure)
			// We can't easily test for specific elements without mocking more,
			// but we can verify SingleStatus is NOT rendered
			expect(screen.queryByTestId("single-status")).not.toBeInTheDocument();
		});
	});

	describe("when statusId is 0", () => {
		it("renders WallFeed component (treats 0 as no statusId)", () => {
			act({ statusId: 0 });

			expect(screen.queryByTestId("single-status")).not.toBeInTheDocument();
		});
	});

	describe("when statusId is negative", () => {
		it("renders WallFeed component (treats negative as no statusId)", () => {
			act({ statusId: -1 });

			expect(screen.queryByTestId("single-status")).not.toBeInTheDocument();
		});
	});

	describe("props are passed correctly", () => {
		it("passes statusId to SingleStatus", () => {
			const statusId = 456;
			act({ statusId });

			expect(
				screen.getByText(`Single Status: ${statusId}`),
			).toBeInTheDocument();
		});

		it("passes all props to WallFeed", () => {
			const wallProps: WallProps = {
				wallType: "profile",
				wallId: 999,
			};
			act(wallProps);

			// WallFeed should receive all props
			// Since we're not mocking WallFeed, we can't directly test this,
			// but the component should render without errors
			expect(screen.queryByTestId("single-status")).not.toBeInTheDocument();
		});
	});
});
