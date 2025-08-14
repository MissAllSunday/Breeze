import type { TabContentProps } from "breezeTypes";
import type React from "react";

export default function Tab(props: TabContentProps): React.JSX.Element {
	return (
		<div className="windowbg">
			<div
				dangerouslySetInnerHTML={{ __html: props.content }}
				className="content"
			/>
		</div>
	);
}
