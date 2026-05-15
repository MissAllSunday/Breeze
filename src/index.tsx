import { createRoot } from "react-dom/client";

import Tab from "./components/Tab";
import Tabs from "./components/Tabs";
import smfVars from "./DataSource/SMF";
import smfTextVars from "./DataSource/Txt";
import Wall from "./Wall";
import Button from "./components/Button";
import React from "react";

const rootElement =
	document.getElementById("root") ?? document.createElement("div");
const root = createRoot(rootElement);
const wallType: string = rootElement.getAttribute("wallType") ?? "profile";
// @ts-expect-error settings are loaded server side
const pagination: number = window.breezePagination ?? 5;

// Parse URL parameters - SMF uses semicolons (;) as delimiters instead of ampersands (&)
const searchString = window.location.search.substring(1); // Remove leading '?'
const urlParams = new URLSearchParams(searchString.replace(/;/g, "&")); // Convert ; to &
const statusId: number = Number(urlParams.get("id")) || 0;

// @ts-ignore settings are loaded server side
const enableBuddiesTab: boolean =
	Boolean(window.breezeEnableBuddiesTab) ?? false;
// @ts-expect-error settings are loaded server side
const enableAboutMeTab: boolean = Boolean(window.breezeAboutMe) ?? false;

if (wallType === "wall") {
	root.render(
		<React.StrictMode>
			<Wall
				wallType={wallType}
				pagination={pagination}
				name={smfTextVars.tabs.wall}
				statusId={statusId}
			/>
		</React.StrictMode>,
	);
} else {
	root.render(
		<React.StrictMode>
			<Tabs
				buttons={
					smfVars.canShowAddBuddyButton ? (
						<Button
							label={"Add buddy"}
							onClick={(): void => {
								throw new Error("Function not implemented.");
							}}
						/>
					) : undefined
				}
			>
				<Wall
					wallType={wallType}
					pagination={pagination}
					name={smfTextVars.tabs.wall}
					statusId={statusId}
				/>
				{enableAboutMeTab && (
					<Tab content={smfVars.aboutMeContent} name={smfTextVars.tabs.about} />
				)}
				{enableBuddiesTab && (
					<Tab
						content={smfVars.buddiesTabContent}
						name={smfTextVars.tabs.buddies}
					/>
				)}
			</Tabs>
		</React.StrictMode>,
	);
}
