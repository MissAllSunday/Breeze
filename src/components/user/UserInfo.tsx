import type { UserInfoProps } from "breezeTypesUser";
import type * as React from "react";
import { useCallback, useState } from "react";
import SmfVars from "../../DataSource/SMF";
import smfTextVars from "../../DataSource/Txt";
import canShowAddBuddyButton from "../../utils/canShowAddBuddyButton";
import { showError, showInfo } from "../../utils/tooltip";
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

	const [localBuddyStatus, setLocalBuddyStatus] = useState(
		props.userData.buddy_status ?? (props.userData.is_buddy ? 'confirmed' : 'none')
	);
	const [isBuddyLoading, setIsBuddyLoading] = useState(false);

	const buddyIconClass = {
		confirmed: 'delete',
		pending: 'clock',
		none: 'plus',
	}[localBuddyStatus];
	const buddyTitle = {
		confirmed: smfTextVars.general.buddyRemove,
		pending: 'Pending',
		none: smfTextVars.general.buddyAdd,
	}[localBuddyStatus];

	const handleBuddyClick = useCallback(async (e: React.MouseEvent<HTMLAnchorElement>) => {
		e.preventDefault();
		if (localBuddyStatus === 'pending' || isBuddyLoading) {
			return;
		}

		setIsBuddyLoading(true);
		const url = `${SmfVars.script_url}?action=buddy;u=${props.userData.id};${SmfVars.session.var}=${SmfVars.session.id}${SmfVars.buddyToken?.var ? `;${SmfVars.buddyToken.var}=${SmfVars.buddyToken.value}` : ''}`;

		try {
			const response = await fetch(url, {
				headers: {
					'X-SMF-AJAX': '1',
				},
			});
			const data = await response.json();

			if (data.token) {
				SmfVars.buddyToken.var = data.token.var;
				SmfVars.buddyToken.value = data.token.value;
			}

			if (data.message) {
				showInfo(data.message);
			}

			setLocalBuddyStatus((prev) => {
				if (prev === 'none') return 'pending';
				if (prev === 'confirmed') return 'none';
				return prev;
			});
		} catch (_error) {
			showError(smfTextVars.error.generic);
		} finally {
			setIsBuddyLoading(false);
		}
	}, [localBuddyStatus, isBuddyLoading, props.userData.id]);

	const buddyButton = canShowAddBuddyButton(props.userData, SmfVars.user_id) ? (
		<>
			<a
				href="#"
				onClick={handleBuddyClick}
				title={buddyTitle}
				style={localBuddyStatus === 'pending' || isBuddyLoading ? { pointerEvents: 'none' } : undefined}
			>
				<span className={`main_icons ${buddyIconClass}`} />
			</a>
			{localBuddyStatus === 'pending' && (
				<span className="smalltext">{smfTextVars.general.invitationPending}</span>
			)}
		</>
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
