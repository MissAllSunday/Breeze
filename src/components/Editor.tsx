import React, { createElement, useCallback, useState } from 'react';

import smfVars from '../DataSource/SMF';
import smfTextVars from '../DataSource/Txt';

const Editor: React.FunctionComponent<any> = (props: { saveContent: (content: string) => boolean, isFull: boolean }) => {
  const [content, setContent] = useState('');
  const handleClick = useCallback(() => {
    if (!window.confirm(smfVars.youSure)) {
      return;
    }

    const toSave = '';
    //const toSave = props.isFull ? smfVars.smfEditorHandler.instance(smfVars.editorElement).val() :
    //  content;
    if (props.saveContent(toSave)) {

      if (props.isFull) {
        smfVars.smfEditorHandler.instance(document.getElementById(smfVars.editorId)).val('');
      } else {
        const input = (document.getElementById('content') as HTMLTextAreaElement);
        input.value = '';
      }
    }
  }, [props]);

  const handleContent = useCallback((event: any) => setContent(event.target.value), []);

  return (
    <div className="post_content">
      {props.isFull ?
        <div dangerouslySetInnerHTML={{ __html: smfVars.editorElement.innerHTML }}/> :
        <textarea id="content" name="content" onChange={handleContent} className="editor"/>
      }
      <div id="post_confirm_buttons">
        <input type="submit" value={smfTextVars.general.send} name="post" className="button"
               onClick={handleClick}/>
      </div>
    </div>
  );
};

export default Editor;
