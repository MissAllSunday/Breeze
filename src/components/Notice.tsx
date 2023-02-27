import { noticeProps } from 'breezeTypes'
import React, { useCallback, useEffect, useState } from 'react'

export default function Notice (props: noticeProps): JSX.Element {
  const [show, setShow] = useState(props.show)

  useEffect(() => {
    setShow(props.show)
  }, [props.show])

  const closeNotice = useCallback(() => {
    setShow(false)
  }, [])

  return (
    show
      ? <div className={ props.options.type}>
    <h3>
      {props.options.header}
      <span className="main_icons remove_button floatright pointer_cursor" onClick={closeNotice} />
    </h3>
    <div className="smalltext">{props.options.body}</div>
  </div>
      : <div></div>
  )
}

Notice.defaultProps =
  {
    options: {
      type: 'notice',
      header: '',
      body: ''
    },
    show: false
  }
