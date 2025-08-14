import "@testing-library/jest-dom";
import type { LikeInfoType } from "breezeTypesLikes";
import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";

import { LikeInfo } from "./LikeInfo";

const MOCK_LIKE_INFO_ITEM: LikeInfoType = {
	count: 0,
	contentId: 1,
	alreadyLiked: false,
	canLike: true,
	type: "lol",
	text: "some text",
	href: "https://missallsunday.com",
	likes: [],
};

function act(overwriteLikeItemTo?: Partial<LikeInfoType>) {
	const likeInfoItem: LikeInfoType = {
		...MOCK_LIKE_INFO_ITEM,
		...overwriteLikeItemTo,
	};

	return render(<LikeInfo likeInfo={likeInfoItem} />);
}

describe("When there are no likes", () => {
	it("shows default 0 likes text", () => {
		act();

		const spanElement = screen.queryByTestId("likesInfo");
		expect(spanElement).not.toBeInTheDocument();
	});
});

describe("When there are likes", () => {
	it("shows number of likes", () => {
		act({ count: 2 });

		const spanElement = screen.queryByTestId("likesInfo");
		expect(spanElement).toBeInTheDocument();
	});
});
