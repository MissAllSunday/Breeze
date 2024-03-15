import React, { createElement, useCallback, useState } from 'react';

import { getEditor } from '../api/Editor';
import smfVars from '../DataSource/SMF';
import Modal from './Modal';

const Editor: React.FunctionComponent<any> = (props: { saveContent: (content: string) => void, isFull: boolean }) => {
  const [content, setContent] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [modalContent, setModalContent] = useState(HTMLDivElement);

  if (props.isFull) {
    const fullEditor = getEditor().then((editorResponse: string) => {
      // setModalContent(document.getElementById('editor_container'));
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

  const onCloseModal = useCallback(
    () => {
      setShowModal(false);
    },
    [],
  );

  return (props.isFull ? (
      <div>
          <Modal
              onClose={onCloseModal}
              show={showModal}
              content={{
                header: 'some header here',
                body: modalContent,
              }}
          />
          <button onClick={() => setShowModal(true)}>click to open modal</button>
      </div>
  ) :
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
