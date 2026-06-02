import type { MentionEntry } from "../api/Mention/Suggest";
import { suggestMembers } from "../api/Mention/Suggest";

type OnMentionInserted = (entry: MentionEntry) => void;

/**
 * Returns an At.js configuration object.
 *
 * `onInsert` is called each time the user picks a suggestion, giving the
 * caller a chance to record the member ID for later server-side verification.
 *
 * The `sorter` is a passthrough because filtering is already done server-side;
 * At.js's default sorter would TypeError on items that lack a `name` key.
 */
export const createAtwhoConfig = (onInsert: OnMentionInserted) => ({
	at: "@",
	startWithSpace: true,
	limit: 10,
	// biome-ignore lint/suspicious/noTemplateCurlyInString: At.js template syntax, not a JS template literal
	displayTpl: "<li>${name}</li>",
	// Default insertTpl is "${atwho-at}${name}" which produces "@name" — no override needed.
	callbacks: {
		// Server-side filtering already returns only matching members;
		// bypass At.js's local re-sort to avoid a TypeError on missing 'name'.
		sorter: (_query: string, items: MentionEntry[]) => items,

		// Evaluate At.js templates and capture the chosen member ID on insert.
		tplEval: (
			tpl: string | ((map: Record<string, string>) => string),
			map: Record<string, string>,
			caller: string,
		): string => {
			if (caller === "onInsert" && map.id) {
				onInsert({ id: parseInt(map.id, 10), name: map.name });
			}
			try {
				const template = typeof tpl === "function" ? tpl(map) : tpl;
				return template.replace(
					/\$\{([^}]*)\}/g,
					(_, key: string) => map[key] ?? "",
				);
			} catch {
				return "";
			}
		},

		remoteFilter: (
			query: string,
			callback: (results: MentionEntry[]) => void,
		): void => {
			if (!query || query.length < 2 || query.length > 60) {
				return;
			}

			suggestMembers(query)
				.then(callback)
				.catch(() => callback([]));
		},
	},
});
