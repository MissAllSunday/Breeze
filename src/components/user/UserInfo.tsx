import type { UserInfoProps } from "breezeTypesUser";
import type * as React from "react";
import { useCallback, useState } from "react";
import SmfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import canShowAddBuddyButton from "../../utils/canShowAddBuddyButton";
import Avatar from "./Avatar";
import MiniProfile from "./MiniProfile";

const UserInfo: React.FunctionComponent<UserInfoProps> = (
	props: UserInfoProps,
) => {
	const [showMiniProfile, setShowMiniProfile] = useState(false);

	const handleOpen = useCallback(() => {
		setShowMiniProfile(true);
	}, []);

	const handleClose = useCallback(() => {
		setShowMiniProfile(false);
	}, []);

	const onlineIndicator = props.userData.online?.is_online ? (
		<span className="mini_profile_online" title={props.userData.online.text}>
			&#x1F7E2;
		</span>
	) : (
		<span className="mini_profile_offline" title={props.userData.online?.text}>
			&#x26AB;
		</span>
	);

	const buddyStatus = props.userData.buddy_status ?? (props.userData.is_buddy ? 'confirmed' : 'none');
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

	const buddyButton = canShowAddBuddyButton(props.userData, SmfVars.user_id) ? (
		<a
			href={buddyStatus !== 'pending' ? `${SmfVars.script_url}?action=buddy;u=${props.userData.id};${SmfVars.session.var}=${SmfVars.session.id}${SmfVars.buddyToken?.var ? `;${SmfVars.buddyToken.var}=${SmfVars.buddyToken.value}` : ''}` : undefined}
			title={buddyTitle}
			style={buddyStatus === 'pending' ? { pointerEvents: 'none' } : undefined}
		>
			<span className={`main_icons ${buddyIconClass}`} />
		</a>
	) : null;

	return (
		<>
			<ul className="user_info">
				<li>
					<button
						type="button"
						onClick={handleOpen}
						className="pointer_cursor"
						style={{ color: props.userData.group_color }}
					>
						{onlineIndicator} {props.userData.name}
					</button>
          &nbsp; {buddyButton}
				</li>
				<li className="avatar">
					<button type="button" onClick={handleOpen} className="pointer_cursor">
						<Avatar
							href={props.userData.avatar.url}
							userName={props.userData.username}
						/>
					</button>
				</li>
				<li
					className="icons"
					dangerouslySetInnerHTML={{ __html: props.userData.group_icons }}
				/>
			</ul>
			<MiniProfile
				userData={props.userData}
				show={showMiniProfile}
				onClose={handleClose}
			/>
		</>
	);
};

export default UserInfo;
