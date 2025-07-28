import React, { useCallback, useEffect, useState } from 'react';
import type { EditorProps } from 'breezeTypesEditor';

import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';
import { showError } from '../utils/tooltip';
const Editor: React.FunctionComponent<EditorProps> = (props: EditorProps) => {
  const [content, setContent] = useState('');

  const handleContent = useCallback((event: React.ChangeEvent<HTMLTextAreaElement>) => setContent(event.target.value), []);
  const textArea = React.useRef(null);

  useEffect(() => {
    if (!props.isFull) {
      return;
    }

    smfVars.smfEditorHandler.create(textArea.current, smfVars.editorOptions);

    if (smfVars.editorOptions.emoticonsEnabled) {
      smfVars.smfEditorHandler.instance(textArea.current).createPermanentDropDown();
    }

    if (!smfVars.editorIsRich) {
      smfVars.smfEditorHandler.instance(textArea.current).toggleSourceMode();
    }
  }, [props.isFull]);

  const handleClick = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }

    const toSave = props.isFull ? smfVars.smfEditorHandler.instance(textArea.current).val() : content;

    if (toSave.length === 0) {
      showError(smfTextVars.error.errorEmpty);

      return;
    }

    const saved = props.saveContent(toSave);

    if (saved) {
      if (props.isFull) {
        smfVars.smfEditorHandler.instance(textArea.current).val('');
      }
      setContent('');
    }
  }, [content, props]);

  return (
    <div className="post_content">
      <textarea
        id="content"
        name="content"
        value={content}
        onChange={handleContent}
        ref={textArea}
        className="editor"
        data-testid="content"/>
      <div id="content_resizer" className="richedit_resize"></div>
      <input type="hidden" name="content_mode" id="content'_mode" value="0"/>
      <div id="post_confirm_buttons">
        <input
          type="submit"
          value={smfTextVars.general.send}
          name="post"
          className="button"
          data-testid="send"
               onClick={handleClick}/>
      </div>
    </div>
  );
};

export default Editor;
