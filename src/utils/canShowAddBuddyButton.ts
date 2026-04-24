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
	// Check 1: Don't show on your own profile.
	if (userData.id === currentUserId) {
		return false;
	}

	// Checks 2 & 3: Block list.
	const blockList = userData.blockList ?? [];
	const blockBuddyRequests = userData.blockBuddyRequests ?? 0;

	if (blockBuddyRequests !== 0 && blockList.includes(currentUserId)) {
		return false;
	}

	return true;
};

export default canShowAddBuddyButton;
