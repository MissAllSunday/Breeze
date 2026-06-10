import type React from "react";
import { useState } from "react";

import ActionBarItem from "./ActionBarItem";

interface BarAction<TCtx> {
	id: string;
	label: string | ((ctx: TCtx) => string);
	icon: string | ((ctx: TCtx) => string);
	testId?: string;
	isVisible: (ctx: TCtx) => boolean;
	onClick?: (ctx: TCtx) => void;
	Panel?: React.ComponentType<TCtx>;
}

interface BarRegistry<TCtx> {
	get: () => BarAction<TCtx>[];
}

interface ActionBarProps<TCtx extends { closePanel: () => void }> {
	registry: BarRegistry<TCtx>;
	baseContext: Omit<TCtx, "closePanel">;
}

export default function ActionBar<TCtx extends { closePanel: () => void }>({
	registry,
	baseContext,
}: ActionBarProps<TCtx>): React.JSX.Element {
	const [openPanelId, setOpenPanelId] = useState<string | null>(null);

	const closePanel = (): void => setOpenPanelId(null);
	const ctx = { ...baseContext, closePanel } as unknown as TCtx;

	const actions = registry.get().filter((a) => a.isVisible(ctx));

	/** Resolves a static string or calls the resolver with the current context. */
	const resolve = (value: string | ((c: TCtx) => string)): string =>
		typeof value === "function" ? value(ctx) : value;

	const handleActivate = (action: BarAction<TCtx>): void => {
		if (action.Panel) {
			setOpenPanelId((prev) => (prev === action.id ? null : action.id));
		} else if (action.onClick) {
			action.onClick(ctx);
		}
	};

	const openAction =
		openPanelId !== null
			? actions.find((a) => a.id === openPanelId)
			: undefined;
	const OpenPanel = openAction?.Panel ?? null;

	return (
		<div className="breeze_action_bar" data-testid="actionBar">
			<ul className="breeze_action_bar__list">
				{actions.map((action) => (
					<ActionBarItem
						key={action.id}
						id={action.id}
						label={resolve(action.label)}
						icon={resolve(action.icon)}
						isPanelOpen={openPanelId === action.id}
						onActivate={() => handleActivate(action)}
						testId={action.testId}
					/>
				))}
			</ul>
			{OpenPanel !== null && (
				<div
					className="breeze_action_bar__panel"
					data-testid="actionBar__panel"
				>
					<OpenPanel {...ctx} />
				</div>
			)}
		</div>
	);
}
