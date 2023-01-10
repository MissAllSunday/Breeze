import React, { useCallback, useState } from 'react'

const Editor: React.FunctionComponent<any> = (props: { saveContent: (content: string) => void }) => {
  const [content, setContent] = useState('')

  const handleClick = useCallback(() => {
    props.saveContent(content)
    const input = (document.getElementById('content') as HTMLInputElement)
    input.value = ''
  }, [props, content])

  const handleContent = useCallback((event: any) => setContent(event.target.value), [])

  return (
    <div className="post_content">
      <textarea id="content" name="content" onChange={handleContent} className="editor" />
      <span id="post_confirm_buttons">
        <span className="post_button_container">
        <button type={'button'} onClick={handleClick}>Save</button>
      </span>
      </span>
    </div>
  )
}

export default Editor
