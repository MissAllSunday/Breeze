import type { UserInfoProps } from "breezeTypesUser";
import type * as React from "react";
import { useCallback, useState } from "react";
import SmfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
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

	const buddyButton = SmfVars.canShowAddBuddyButton ? (
		<a
			href={`${SmfVars.script_url}?action=buddy;u=${props.userData.id}`}
			title={props.userData.is_buddy
				? smfTextVars.general.buddyRemove
				: smfTextVars.general.buddyAdd}
		>
			<span className={`main_icons ${props.userData.is_buddy ? 'delete' : 'plus'}`} />
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
