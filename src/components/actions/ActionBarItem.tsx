import type React from "react";

interface ActionBarItemProps {
	id: string;
	label: string;
	icon: string;
	isPanelOpen: boolean;
	onActivate: () => void;
	/** Forwarded as data-testid on the button — preserves existing test selectors. */
	testId?: string;
}

export default function ActionBarItem({
	id,
	label,
	icon,
	isPanelOpen,
	onActivate,
	testId,
}: ActionBarItemProps): React.JSX.Element {
	const itemClass = [
		"breeze_action_bar__item",
		isPanelOpen ? "breeze_action_bar__item--active" : "",
	]
		.filter(Boolean)
		.join(" ");

	return (
		<li className={itemClass} data-action-id={id}>
			<button
				type="button"
				className="pointer_cursor breeze_action_bar__button"
				onClick={onActivate}
				aria-pressed={isPanelOpen}
				data-testid={testId}
			>
				<span className={`main_icons breeze_action_bar__icon ${icon}`} aria-hidden="true" />
				<span className="breeze_action_bar__label">{label}</span>
			</button>
		</li>
	);
}
