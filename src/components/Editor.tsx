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
    <div>
      <textarea id="content" name="content" onChange={handleContent} />

      <button type={'button'} onClick={handleClick}>Save</button>
    </div>
  )
}

export default Editor
