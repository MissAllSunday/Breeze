import { describe, expect, it } from "vitest";
import type { UserDataType } from "breezeTypesUser";
import canShowAddBuddyButton from "./canShowAddBuddyButton";

const makeUser = (overrides: Partial<UserDataType> = {}): UserDataType => ({
	avatar: {
		href: "https://example.com",
		image: document.createElement("img"),
		name: "avatar",
		url: "https://example.com",
	},
	buddies: [],
	custom_fields: [],
	email: "test@test.com",
	group: "member",
	group_color: "",
	group_icons: "",
	group_id: "0",
	href: "",
	id: 2,
	is_activated: "1",
	is_banned: false,
	is_buddy: false,
	is_guest: false,
	is_reverse_buddy: false,
	last_login_timestamp: "0",
	legacy_url: "",
	link: "",
	link_color: "",
	name: "Test",
	name_color: "",
	online: {
		href: "#",
		is_online: false,
		label: "",
		link: document.createElement("a"),
		member_online_text: "",
		text: "",
	},
	signature: "",
	title: "",
	username: "test",
	username_color: document.createElement("span"),
	...overrides,
});

describe("canShowAddBuddyButton", () => {
	it("returns false when the displayed user is the current user", () => {
		const user = makeUser({ id: 1 });
		expect(canShowAddBuddyButton(user, 1)).toBe(false);
	});

	it("returns true when the displayed user is already a buddy", () => {
		const user = makeUser({ id: 2, is_buddy: true });
		expect(canShowAddBuddyButton(user, 1)).toBe(true);
	});

	it("returns true for a different user who is not a buddy", () => {
		const user = makeUser({ id: 2, is_buddy: false });
		expect(canShowAddBuddyButton(user, 1)).toBe(true);
	});

	it("returns false when the current user is in the displayed user's block list and blockBuddyRequests is enabled", () => {
		const user = makeUser({
			id: 2,
			is_buddy: false,
			blockList: [1, 5],
			blockBuddyRequests: 1,
		});
		expect(canShowAddBuddyButton(user, 1)).toBe(false);
	});

	it("returns true when blockBuddyRequests is disabled even if in block list", () => {
		const user = makeUser({
			id: 2,
			is_buddy: false,
			blockList: [1, 5],
			blockBuddyRequests: 0,
		});
		expect(canShowAddBuddyButton(user, 1)).toBe(true);
	});

	it("returns true when not in block list even if blockBuddyRequests is enabled", () => {
		const user = makeUser({
			id: 2,
			is_buddy: false,
			blockList: [5, 10],
			blockBuddyRequests: 1,
		});
		expect(canShowAddBuddyButton(user, 1)).toBe(true);
	});
});
