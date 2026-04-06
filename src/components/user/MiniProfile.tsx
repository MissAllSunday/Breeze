import type { CustomFieldType, MiniProfileProps } from "breezeTypesUser";
import type React from "react";

import { Modal } from "../Modal";
import Avatar from "./Avatar";

const MiniProfile: React.FunctionComponent<MiniProfileProps> = (
	props: MiniProfileProps,
) => {
	const { userData } = props;

	const onlineIndicator = userData.online?.is_online ? (
		<span className="mini_profile_online" title={userData.online.text}>
			&#x1F7E2;
		</span>
	) : (
		<span className="mini_profile_offline" title={userData.online?.text}>
			&#x26AB;
		</span>
	);

	const profileBody = (
		<div className="mini_profile_body">
			<div className="mini_profile_header">
				<Avatar
					href={userData.avatar.url}
					userName={userData.username}
					customClassName="mini_profile_avatar"
				/>
				<div className="mini_profile_identity">
					<span
						className="mini_profile_name"
						dangerouslySetInnerHTML={{ __html: userData.link_color }}
					/>
					<span className="mini_profile_group">
						{userData.group} {onlineIndicator}
					</span>
					<span
						className="mini_profile_icons"
						dangerouslySetInnerHTML={{ __html: userData.group_icons }}
					/>
				</div>
			</div>

			{userData.title && (
				<div className="mini_profile_field">
					<span className="mini_profile_label">Title</span>
					<span className="mini_profile_value">{userData.title}</span>
				</div>
			)}

			{userData.signature && (
				<div className="mini_profile_field">
					<span className="mini_profile_label">Signature</span>
					<span
						className="mini_profile_value"
						dangerouslySetInnerHTML={{ __html: userData.signature }}
					/>
				</div>
			)}

			{userData.last_login_timestamp && (
				<div className="mini_profile_field">
					<span className="mini_profile_label">Last Active</span>
					<span className="mini_profile_value">
						{userData.last_login_timestamp}
					</span>
				</div>
			)}

			{userData.custom_fields?.length > 0 && (
				<div className="mini_profile_field">
					<span className="mini_profile_label">Custom Fields</span>
					<ul className="mini_profile_custom_fields">
						{userData.custom_fields.map((field: CustomFieldType) => (
							<li key={field.col_name}>
								<strong>{field.title}</strong>:{" "}
								<span dangerouslySetInnerHTML={{ __html: field.value }} />
							</li>
						))}
					</ul>
				</div>
			)}

			{userData.href && (
				<div className="mini_profile_actions">
					<a href={userData.href} className="button">
						View Full Profile
					</a>
				</div>
			)}
		</div>
	);

	const headerContent = userData.name || userData.username;

	return (
		<Modal
			onClose={props.onClose}
			show={props.show}
			content={{
				header: headerContent,
				body: profileBody,
			}}
		/>
	);
};

export default MiniProfile;

