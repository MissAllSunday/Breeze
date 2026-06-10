import "@testing-library/jest-dom";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

import { createRegistry } from "./actionRegistry";
import ActionBar from "./ActionBar";

// Minimal context used only in tests — no dependency on breezeTypes
interface TestCtx {
	closePanel: () => void;
}

type TestBarAction = {
	id: string;
	label: string;
	icon: string;
	order: number;
	isVisible: (ctx: TestCtx) => boolean;
	onClick?: (ctx: TestCtx) => void;
	Panel?: React.ComponentType<TestCtx>;
};

const makeAction = (
	overrides: Partial<TestBarAction> = {},
): TestBarAction => ({
	id: "test",
	label: "Test",
	icon: "🔧",
	order: 0,
	isVisible: () => true,
	...overrides,
});

// Panel that exposes its own close button to test the closePanel callback
const PanelWithClose = ({ closePanel }: TestCtx) => (
	<div data-testid="panel-content">
		<button type="button" data-testid="close-panel-btn" onClick={closePanel}>
			Close
		</button>
	</div>
);

const PanelAlpha = () => <div data-testid="panel-alpha">Alpha</div>;
const PanelBeta = () => <div data-testid="panel-beta">Beta</div>;

function renderBar(actions: TestBarAction[]) {
	const registry = createRegistry<TestBarAction>();
	registry.register(...actions);
	return render(
		<ActionBar<TestCtx> registry={registry} baseContext={{}} />,
	);
}

describe("ActionBar", () => {
	it("renders the action bar container", () => {
		renderBar([makeAction()]);
		expect(screen.getByTestId("actionBar")).toBeInTheDocument();
	});

	it("renders only visible actions", () => {
		renderBar([
			makeAction({ id: "visible", label: "Visible", isVisible: () => true }),
			makeAction({ id: "hidden", label: "Hidden", isVisible: () => false }),
		]);
		expect(screen.getByText("Visible")).toBeInTheDocument();
		expect(screen.queryByText("Hidden")).not.toBeInTheDocument();
	});

	it("renders each action's icon and label", () => {
		const { container } = renderBar([makeAction({ icon: "post_button", label: "Comment" })]);
		expect(container.querySelector(".main_icons.post_button")).toBeInTheDocument();
		expect(screen.getByText("Comment")).toBeInTheDocument();
	});

	it("clicking an onClick-only action fires onClick and does not open a panel", async () => {
		const onClick = vi.fn();
		renderBar([makeAction({ onClick })]);
		await userEvent.click(screen.getByRole("button", { name: /test/i }));
		expect(onClick).toHaveBeenCalledOnce();
		expect(screen.queryByTestId("actionBar__panel")).not.toBeInTheDocument();
	});

	it("clicking a Panel action opens the panel", async () => {
		renderBar([makeAction({ Panel: PanelWithClose })]);
		await userEvent.click(screen.getByRole("button", { name: /test/i }));
		expect(screen.getByTestId("actionBar__panel")).toBeInTheDocument();
		expect(screen.getByTestId("panel-content")).toBeInTheDocument();
	});

	it("clicking an already-open Panel action closes it", async () => {
		renderBar([makeAction({ Panel: PanelWithClose })]);
		const btn = screen.getByRole("button", { name: /test/i });
		await userEvent.click(btn);
		expect(screen.getByTestId("actionBar__panel")).toBeInTheDocument();
		await userEvent.click(btn);
		expect(screen.queryByTestId("actionBar__panel")).not.toBeInTheDocument();
	});

	it("only one panel is open at a time", async () => {
		renderBar([
			makeAction({ id: "alpha", label: "Alpha", order: 10, Panel: PanelAlpha }),
			makeAction({ id: "beta", label: "Beta", order: 20, Panel: PanelBeta }),
		]);
		await userEvent.click(screen.getByRole("button", { name: /alpha/i }));
		expect(screen.getByTestId("panel-alpha")).toBeInTheDocument();

		await userEvent.click(screen.getByRole("button", { name: /beta/i }));
		expect(screen.queryByTestId("panel-alpha")).not.toBeInTheDocument();
		expect(screen.getByTestId("panel-beta")).toBeInTheDocument();
	});

	it("closePanel from within the panel closes it", async () => {
		renderBar([makeAction({ Panel: PanelWithClose })]);
		await userEvent.click(screen.getByRole("button", { name: /test/i }));
		expect(screen.getByTestId("panel-content")).toBeInTheDocument();
		await userEvent.click(screen.getByTestId("close-panel-btn"));
		expect(screen.queryByTestId("actionBar__panel")).not.toBeInTheDocument();
	});

	it("no panel is shown by default", () => {
		renderBar([makeAction({ Panel: PanelWithClose })]);
		expect(screen.queryByTestId("actionBar__panel")).not.toBeInTheDocument();
	});

	it("renders an empty bar when registry is empty", () => {
		renderBar([]);
		expect(screen.getByTestId("actionBar")).toBeInTheDocument();
		expect(screen.queryByRole("button")).not.toBeInTheDocument();
	});
});
