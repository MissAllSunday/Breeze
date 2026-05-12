import "@testing-library/jest-dom";
import type { UserDataType } from "breezeTypesUser";
import { render, screen, cleanup } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi, afterEach } from "vitest";

import { userData } from "../../__fixtures__/userData";
import UserInfo from "./UserInfo";

vi.mock("../../DataSource/Txt", () => ({
	default: {
		general: {
			close: "Close",
			buddyAdd: "Add to buddy list",
			buddyRemove: "Remove from buddy list",
		},
	},
}));

vi.mock("../../DataSource/SMF", () => ({
	default: {
		script_url: "http://smf.local:8000/index.php",
		user_id: 1,
		session: { var: "sc", id: "test_session_123" },
		buddyToken: { var: "buddy_token_var", value: "test_buddy_token_456" },
	},
}));

function act(overrides?: Partial<UserDataType>) {
	const data: UserDataType = { ...userData.basic, ...overrides };

	return render(<UserInfo userData={data} />);
}

describe("UserInfo", () => {
	afterEach(() => {
		cleanup();
	});
	describe("rendering", () => {
		it("renders the user name", () => {
			act({ name: "Astaroth" });

			const names = screen.getAllByText(/Astaroth/);
			expect(names[0]).toBeInTheDocument();
		});

		it("renders the avatar", () => {
			act();

			const avatars = screen.getAllByAltText(userData.basic.username);
			expect(avatars[0]).toBeInTheDocument();
			expect(avatars[0]).toHaveAttribute("src", userData.basic.avatar.url);
		});

		it("renders group icons", () => {
			act({ group_icons: '<i class="icon">star</i>' });

			const stars = screen.getAllByText("star");
			expect(stars[0]).toBeInTheDocument();
		});

		it("applies group color to the name button", () => {
			act({ group_color: "#FF0000" });

			const buttons = screen.getAllByRole("button", { name: /name/i });
			expect(buttons[0]).toHaveStyle({ color: "rgb(255, 0, 0)" });
		});
	});

	describe("online indicator", () => {
		it("shows online indicator when user is online", () => {
			act({
				online: {
					...userData.basic.online,
					is_online: true,
					text: "Online now",
				},
			});

			const onlineSpans = screen.getAllByTitle("Online now");
			expect(onlineSpans[0]).toHaveClass("mini_profile_online");
		});

		it("shows offline indicator when user is offline", () => {
			act({
				online: {
					...userData.basic.online,
					is_online: false,
					text: "Offline",
				},
			});

			const offlineSpans = screen.getAllByTitle("Offline");
			expect(offlineSpans[0]).toHaveClass("mini_profile_offline");
		});
	});

	describe("buddy link", () => {
		it("renders buddy icon with 'Add to buddy list' title when user is not a buddy", () => {
			act({ id: 2, is_buddy: false });

			const links = screen.getAllByTitle("Add to buddy list");
			// Get the first link (from UserInfo, not MiniProfile)
			const link = links[0];
			expect(link).toBeInTheDocument();
			expect(link).toHaveAttribute(
				"href",
				"http://smf.local:8000/index.php?action=buddy;u=2;sc=test_session_123;buddy_token_var=test_buddy_token_456",
			);

			const icon = link.querySelector(".main_icons.plus");
			expect(icon).toBeInTheDocument();
		});

		it("renders buddy icon with 'Remove from buddy list' title when user is a buddy", () => {
			act({ id: 3, is_buddy: true });

			const links = screen.getAllByTitle("Remove from buddy list");
			// Get the first link (from UserInfo, not MiniProfile)
			const link = links[0];
			expect(link).toBeInTheDocument();
			expect(link).toHaveAttribute(
				"href",
				"http://smf.local:8000/index.php?action=buddy;u=3;sc=test_session_123;buddy_token_var=test_buddy_token_456",
			);

			const icon = link.querySelector(".main_icons.delete");
			expect(icon).toBeInTheDocument();
		});

		it("does not render buddy link when viewing own profile", () => {
			act({ id: 1, is_buddy: false });

			expect(screen.queryByTitle("Add to buddy list")).not.toBeInTheDocument();
			expect(
				screen.queryByTitle("Remove from buddy list"),
			).not.toBeInTheDocument();
		});


	});

	describe("mini profile modal", () => {
		it("opens MiniProfile when the name button is clicked", async () => {
			const user = userEvent.setup();
			act({ name: "Astaroth" });

			const nameButtons = screen.getAllByRole("button", { name: /Astaroth/i });
			await user.click(nameButtons[0]);

			const dialog = screen.getByRole("dialog");
			expect(dialog).not.toHaveClass("hide");
		});

		it("opens MiniProfile when the avatar button is clicked", async () => {
			const user = userEvent.setup();
			act();

			const avatarButtons = screen.getAllByRole("button", {
				name: userData.basic.username,
			});
			await user.click(avatarButtons[0]);

			const dialog = screen.getByRole("dialog");
			expect(dialog).not.toHaveClass("hide");
		});

		it("does not show MiniProfile by default", () => {
			act();

			const dialog = screen.getByRole("dialog");
			expect(dialog).toHaveClass("hide");
		});
	});
});
