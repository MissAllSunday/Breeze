import type { UserDataType } from "breezeTypesUser";

/**
 * Frontend port of ProfileService::canShowAddBuddyButton.
 *
 * Determines whether the "Add buddy / Remove buddy" icon should be shown next
 * to a user rendered in inline components (status cards, mini-profiles).
 *
 * Rules (block always beats buddy):
 *  1. Never show the button on your own profile.
 *  2. Never show the button if the displayed user has blocked the current user.
 *  3. Otherwise show the button (it acts as "add" or "remove" depending on is_buddy).
 *
 * @param userData      The user being displayed (must include blockList from the API).
 * @param currentUserId The logged-in user's ID (from SmfVars.user_id).
 * @returns true if the buddy button should be visible.
 */
const canShowAddBuddyButton = (
	userData: UserDataType,
	currentUserId: number,
): boolean => {
	// Check 1: Don't show the button on your own profile.
	if (userData.id === currentUserId) {
		return false;
	}

	// Check 2: Block always beats buddy — if the displayed user has blocked
	// the current user, hide the button unconditionally.
	if (userData.blockList?.includes(currentUserId)) {
		return false;
	}

	return true;
};

export default canShowAddBuddyButton;
