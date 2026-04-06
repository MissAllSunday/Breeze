import "@testing-library/jest-dom";
import type { UserDataType } from "breezeTypesUser";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

import { userData } from "../../__fixtures__/userData";
import MiniProfile from "./MiniProfile";

vi.mock("../../DataSource/Txt", () => ({
	default: {
		general: { close: "Close" },
	},
}));

function act(show = true, overrides?: Partial<UserDataType>) {
	const onClose = vi.fn();
	const data: UserDataType = { ...userData.basic, ...overrides };

	const result = render(
		<MiniProfile userData={data} show={show} onClose={onClose} />,
	);

	return { onClose, result };
}

describe("MiniProfile", () => {
	describe("when show is true", () => {
		it("renders the modal with the user name as header", () => {
			act(true, { name: "Astaroth" });

			expect(screen.getByRole("dialog")).toBeInTheDocument();
			expect(screen.getByText("Astaroth")).toBeInTheDocument();
		});

		it("renders the avatar", () => {
			act(true);

			const avatar = screen.getByAltText(userData.basic.username);
			expect(avatar).toBeInTheDocument();
			expect(avatar).toHaveAttribute("src", userData.basic.avatar.url);
		});

		it("renders the user group", () => {
			act(true, { group: "Global Moderator" });

			expect(screen.getByText(/Global Moderator/)).toBeInTheDocument();
		});

		it("renders the title when present", () => {
			act(true, { title: "Forum Veteran" });

			expect(screen.getByText("Forum Veteran")).toBeInTheDocument();
		});

		it("does not render the title when empty", () => {
			act(true, { title: "" });

			expect(screen.queryByText("Title")).not.toBeInTheDocument();
		});

		it("renders the signature when present", () => {
			act(true, { signature: "My cool signature" });

			expect(screen.getByText("My cool signature")).toBeInTheDocument();
		});

		it("does not render the signature section when empty", () => {
			act(true, { signature: "" });

			expect(screen.queryByText("Signature")).not.toBeInTheDocument();
		});

		it("renders last active timestamp when present", () => {
			act(true, { last_login_timestamp: "1695828664" });

			expect(screen.getByText("1695828664")).toBeInTheDocument();
		});

		it("does not render last active when empty", () => {
			act(true, { last_login_timestamp: "" });

			expect(screen.queryByText("Last Active")).not.toBeInTheDocument();
		});

		it("renders custom fields when present", () => {
			act(true, {
				custom_fields: [
					{ title: "Location", col_name: "cust_loc", value: "Earth", simple: "Earth", raw: "Earth", placement: "standard" },
					{ title: "Mood", col_name: "cust_mood", value: "Happy", simple: "Happy", raw: "Happy", placement: "standard" },
				],
			});

			expect(screen.getByText("Location")).toBeInTheDocument();
			expect(screen.getByText("Earth")).toBeInTheDocument();
			expect(screen.getByText("Mood")).toBeInTheDocument();
			expect(screen.getByText("Happy")).toBeInTheDocument();
		});

		it("does not render custom fields section when empty", () => {
			act(true, { custom_fields: [] });

			expect(screen.queryByText("Custom Fields")).not.toBeInTheDocument();
		});

		it("renders full profile link when href is present", () => {
			act(true, { href: "https://forum.test/profile/1" });

			const link = screen.getByText("View Full Profile");
			expect(link).toBeInTheDocument();
			expect(link).toHaveAttribute("href", "https://forum.test/profile/1");
		});
	});

	describe("online indicator", () => {
		it("shows online indicator when user is online", () => {
			act(true, {
				online: {
					...userData.basic.online,
					is_online: true,
					text: "Online now",
				},
			});

			const onlineSpan = screen.getByTitle("Online now");
			expect(onlineSpan).toHaveClass("mini_profile_online");
		});

		it("shows offline indicator when user is offline", () => {
			act(true, {
				online: {
					...userData.basic.online,
					is_online: false,
					text: "Offline",
				},
			});

			const offlineSpan = screen.getByTitle("Offline");
			expect(offlineSpan).toHaveClass("mini_profile_offline");
		});
	});

	describe("when show is false", () => {
		it("renders the modal with hide class", () => {
			act(false);

			const dialog = screen.getByRole("dialog");
			expect(dialog).toHaveClass("hide");
		});
	});

	describe("close behavior", () => {
		it("calls onClose when the close button is clicked", async () => {
			const user = userEvent.setup();
			const { onClose } = act(true);

			const closeButton = screen.getByTitle("Close");
			await user.click(closeButton);

			expect(onClose).toHaveBeenCalledTimes(1);
		});
	});
});

