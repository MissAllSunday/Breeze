import type { CustomFieldType, MiniProfileProps } from "breezeTypesUser";
import type React from "react";
import SmfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import canShowAddBuddyButton from "../../utils/canShowAddBuddyButton";
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

	const buddyStatus = userData.buddy_status ?? (userData.is_buddy ? 'confirmed' : 'none');
	const buddyIconClass = {
		confirmed: 'delete',
		pending: 'clock',
		none: 'plus',
	}[buddyStatus];
	const buddyTitle = {
		confirmed: smfTextVars.general.buddyRemove,
		pending: 'Pending',
		none: smfTextVars.general.buddyAdd,
	}[buddyStatus];

	const buddyButton = canShowAddBuddyButton(userData, SmfVars.user_id) ? (
			<a
				href={buddyStatus !== 'pending' ? `${SmfVars.script_url}?action=buddy;u=${userData.id};${SmfVars.session.var}=${SmfVars.session.id}${SmfVars.buddyToken?.var ? `;${SmfVars.buddyToken.var}=${SmfVars.buddyToken.value}` : ''}` : undefined}
				title={buddyTitle}
				style={buddyStatus === 'pending' ? { pointerEvents: 'none' } : undefined}
			>
				<span className={`main_icons ${buddyIconClass}`} />
			</a>
	) : null;

	const profileBody = (
		<div className="mini_profile_body">
			<div className="post_wrapper">
				<div className="poster">
					<ul className="user_info">
						<li className="avatar">
							<a href={SmfVars.script_url + userData.legacy_url}>
								<Avatar
									href={userData.avatar.url}
									userName={userData.username}
									customClassName="mini_profile_avatar"
								/>
							</a>
						</li>
						<li>
							<a
								href={SmfVars.script_url + userData.legacy_url}
								className="pointer_cursor"
								style={{ color: props.userData.group_color }}
							>
								{onlineIndicator} {props.userData.name}
							</a>
              &nbsp;{buddyButton}
						</li>
						<li className="postgroup">{userData.group}</li>
						<li
							className="icons"
							dangerouslySetInnerHTML={{ __html: userData.group_icons }}
						/>
						{userData.title && (
							<li className="breeze_description">{userData.title}</li>
						)}

						{userData.custom_fields?.length > 0 && (
							<div className="mini_profile_field">
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
					</ul>
				</div>
				<div className="postarea">
					{userData.signature && (
						<div className="mini_profile_field">
							<span
								className="mini_profile_value"
								dangerouslySetInnerHTML={{ __html: userData.signature }}
							/>
						</div>
					)}
				</div>
			</div>
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
