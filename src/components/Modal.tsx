import { modalProps } from 'breezeTypes'
import React, { useCallback, useEffect, useState } from 'react'

import smfTextVars from '../DataSource/Txt'

const Modal: React.FunctionComponent<modalProps> = (props: modalProps) => {
  const [style, setStyle] = useState(props.show ? 'show' : 'hide')

  useEffect(() => {
    setStyle(props.show ? 'show' : 'hide')
  }, [props.show])

  const handleClose = useCallback(
    () => {
      setStyle('hide')
      props.onClose()
    },
    [props]
  )

  const handleParentClick = useCallback(
    (event: React.MouseEvent) => {
      event.preventDefault()

      if (event.target === event.currentTarget) {
        handleClose()
      }
    },
    [handleClose]
  )

  return (
    <div id="smf_popup" className={'popup_container ' + style} onClick={handleParentClick}>
      <div className="popup_window description">
        <div className="catbg popup_heading">
          {props.content.header}
          <a className="main_icons hide_popup" onClick={handleClose} title={smfTextVars.general.close} href="/#"> </ a>
        </div>
        <div className="popup_content clear">
          {props.content.body}
        </div>
      </div>
    </div>
  )
}

export default Modal
