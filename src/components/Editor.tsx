import type { EditorProps } from "breezeTypesEditor";
import React, { useCallback, useEffect, useRef } from "react";
import type { MentionEntry } from "../api/Mention/Suggest";
import smfVars from "../DataSource/SMF";
import smfTextVars from "../DataSource/Txt";
import { createAtwhoConfig } from "../utils/mentionConfig";
import { showError } from "../utils/tooltip";

const Editor: React.FunctionComponent<EditorProps> = (props: EditorProps) => {
	const textArea = useRef<HTMLTextAreaElement>(null);
	const mentionedMembers = useRef<MentionEntry[]>([]);

	useEffect(() => {
		if (props.isFull) {
			smfVars.smfEditorHandler.create(textArea.current, smfVars.editorOptions);

			if (smfVars.editorOptions.emoticonsEnabled) {
				smfVars.smfEditorHandler
					.instance(textArea.current)
					.createPermanentDropDown();
			}

			if (!smfVars.editorIsRich) {
				smfVars.smfEditorHandler.instance(textArea.current).toggleSourceMode();
			}
		}

		type JQueryResult = {
			atwho: (cfg: object) => JQueryResult;
			find: (sel: string) => JQueryResult;
			[index: number]: HTMLElement;
		};
		type JQuery$ = ((selector: unknown) => JQueryResult) & {
			fn?: { atwho?: unknown };
		};
		const $ = (window as Window & { $?: JQuery$ }).$;
		if ($ == null || typeof $.fn?.atwho !== "function") {
			return;
		}

		const atwhoConfig = createAtwhoConfig((entry) => {
			mentionedMembers.current.push(entry);
		});

		$(textArea.current).atwho(atwhoConfig);

		if (props.isFull) {
			$(".sceditor-container").find("textarea").atwho(atwhoConfig);
			const iframe = $(".sceditor-container").find("iframe")[0] as
				| HTMLIFrameElement
				| undefined;
			if (iframe !== undefined) {
				$(iframe.contentDocument?.body).atwho(atwhoConfig);
			}
		}
	}, [props.isFull]);

	const handleClick = useCallback(() => {
		const toSave = props.isFull
			? smfVars.smfEditorHandler.instance(textArea.current).val()
			: (textArea.current?.value ?? "");

		if (toSave === "about:suki") {
			return alert(
				"What if everything around you\n" +
					"Isn't quite as it seems?\n" +
					"What if all the world you think you know\n" +
					"Is an elaborate dream?",
			);
		}

		if (smfVars.confirmPost && !window.confirm(smfVars.youSure)) {
			return;
		}

		if (toSave.length === 0) {
			showError(smfTextVars.error.errorEmpty);
			return;
		}

		// Deduplicate and filter: only keep members whose @Name is still in the body.
		const seen = new Set<number>();
		const mentionIds = mentionedMembers.current
			.filter((m) => {
				if (seen.has(m.id) || !toSave.includes(`@${m.name}`)) {
					return false;
				}
				seen.add(m.id);
				return true;
			})
			.map((m) => m.id);

		const saved = props.saveContent(toSave, mentionIds);

		if (saved) {
			if (props.isFull) {
				smfVars.smfEditorHandler.instance(textArea.current).val("");
			} else if (textArea.current) {
				textArea.current.value = "";
			}
			mentionedMembers.current = [];
		}
	}, [props]);

	return (
		<div className="post_content">
			<textarea
				id="content"
				name="content"
				ref={textArea}
				className="editor"
				data-testid="content"
			/>
			<div id="content_resizer" className="richedit_resize"></div>
			<input type="hidden" name="content_mode" id="content'_mode" value="0" />
			<div id="post_confirm_buttons">
				<input
					type="submit"
					value={smfTextVars.general.send}
					name="post"
					className="button"
					data-testid="send"
					onClick={handleClick}
				/>
			</div>
		</div>
	);
};

export default React.memo(Editor);
