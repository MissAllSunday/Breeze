import smfVars from "../../DataSource/SMF";

export interface MentionEntry {
	id: number;
	name: string;
}

export const suggestMembers = async (
	query: string,
): Promise<MentionEntry[]> => {
	const url =
		`${smfVars.script_url}?action=suggest` +
		`;${smfVars.session.var}=${smfVars.session.id};xml` +
		`&search=${encodeURIComponent(query)}&suggest_type=member`;

	const response = await fetch(url, { headers: { "X-SMF-AJAX": "1" } });
	const xml = await response.text();
	const doc = new DOMParser().parseFromString(xml, "text/xml");

	return Array.from(doc.querySelectorAll("item")).map((item) => ({
		id: parseInt(item.getAttribute("id") ?? "0", 10),
		name: item.textContent ?? "",
	}));
};
