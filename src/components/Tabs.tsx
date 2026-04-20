import type { ReactElement } from "react";
import { Children, useCallback, useEffect, useState } from "react";
import type Tab from "./Tab";

interface TabType {
	active: boolean;
	contentElement: ReactElement;
	href: string;
	index: number;
	name: string;
}

type TabsType = TabType[];

function Tabs(props: {
	children: Array<ReactElement<typeof Tab>>;
	buttons?: ReactElement;
}): ReactElement {
	const [tabs, setTabs] = useState<TabsType>([]);

	useEffect(() => {
		const initialTabs: TabsType = [];

		Children.forEach(
			props.children,
			(child: ReactElement<typeof Tab>, index: number) => {
				if (child === null) {
					return;
				}

				initialTabs.push({
					index,
					href: `#tab-${index}`,
					name: child.props.name,
					active: index === 0,
					contentElement: child,
				});
			},
		);

		setTabs(initialTabs);
	}, [props.children]);

	const changeTab = useCallback(
		(clickedTab: TabType) => {
			if (clickedTab.active) {
				return;
			}

			setTabs(
				tabs.map((tab: TabType) => {
					tab.active = tab.index === clickedTab.index;
					return tab;
				}),
			);
		},
		[tabs],
	);

	return (
		<>
			<div id="Breeze_tabs" className="generic_menu">
				<ul className="dropmenu breezeTabs">
					{tabs.map((tab: TabType) => (
						<li className="subsections" key={tab.href}>
							<a
								href={tab.href}
								className={tab.active ? "active" : ""}
								onClick={() => changeTab(tab)}
							>
								{tab.name}
							</a>
						</li>
					))}
					{props.buttons && (
						<li className="subsections">
							{props.buttons}
						</li>
					)}
				</ul>
			</div>
			<ul>
				{tabs.map((tab: TabType) => (
					<li
						key={tab.index}
						id={`#tab-${tab.index}`}
						className={tab.active ? "show" : "hide"}
					>
						{tab.contentElement}
					</li>
				))}
			</ul>
		</>
	);
}

export default Tabs;
