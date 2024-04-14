import React, { createElement, useCallback, useState } from 'react';

import { getEditor } from '../api/Editor';
import smfVars from '../DataSource/SMF';
import Modal from './Modal';

const Editor: React.FunctionComponent<any> = (props: { saveContent: (content: string) => boolean }) => {
  const [content, setContent] = useState('');

  const handleClick = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }

    if (props.saveContent(content)) {
      const input = (document.getElementById('content') as HTMLTextAreaElement);
      input.value = '';
    }
  }, [props, content]);

  const handleContent = useCallback((event: any) => setContent(event.target.value), []);

  return (
    <div className="post_content">
      <textarea id="content" name="content" onChange={handleContent} className="editor" />
      <span id="post_confirm_buttons">
        <span className="post_button_container">
          <button type="button" onClick={handleClick}>Save</button>
        </span>
      </span>
    </div>
  );
};

export default Editor;
