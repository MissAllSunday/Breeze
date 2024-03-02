import React, { useCallback, useState } from 'react';

import { getEditor } from '../api/Editor';
import smfVars from '../DataSource/SMF';

const Editor: React.FunctionComponent<any> = (props: { saveContent: (content: string) => void, isFull: boolean }) => {
  const [content, setContent] = useState('');

  if (props.isFull) {
    const fullEditor = getEditor().then((editorResponse: any) => {
      console.log(editorResponse);
    });
  }

  const handleClick = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }

    props.saveContent(content);
    const input = (document.getElementById('Breeze') as HTMLInputElement);
    input.value = '';
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
