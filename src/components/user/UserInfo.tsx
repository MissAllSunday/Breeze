import type { UserInfoProps } from "breezeTypesUser";
import type * as React from "react";
import { useCallback, useState } from "react";

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
						{props.userData.name}
					</button>
				</li>
				<li className="avatar">
					<button
						type="button"
						onClick={handleOpen}
						className="pointer_cursor"
					>
						<Avatar
							href={props.userData.avatar.url}
							userName={props.userData.username}
						/>
					</button>
				</li>

				<li className="postgroup">{props.userData.group}</li>
				<li
					className="icons"
					dangerouslySetInnerHTML={{ __html: props.userData.group_icons }}
				/>
				<li className="breeze_description">{props.userData.title}</li>
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
