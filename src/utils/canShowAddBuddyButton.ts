import type { UserDataType } from "breezeTypesUser";

/**
 * Frontend port of ProfileService::canShowAddBuddyButton.
 *
 * Determines whether the "Add buddy" icon should be shown next to a user.
 *
 * @param userData   The user being displayed.
 * @param currentUserId The logged-in user's ID (from SmfVars.user_id).
 * @returns true if the buddy button should be visible.
 */
const canShowAddBuddyButton = (
	userData: UserDataType,
	currentUserId: number,
): boolean => {
	// Don't show the button on your own profile.
	return userData.id !== currentUserId;
};

export default canShowAddBuddyButton;
