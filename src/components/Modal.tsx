import React, { useCallback, useState } from 'react'
import { modalProps } from 'breezeTypes'
import smfTextVars from '../DataSource/Txt'

const Modal: React.FunctionComponent<modalProps> = ({ isShowing, header, body }: modalProps) => {
  const [style, setStyle] = useState(isShowing ? 'hide' : 'show')

  const handleClose = useCallback(
    () => {
      setStyle((prevStyle: string) => 'hide')
    },
    []
  )

  return (
    <div id="smf_popup" className={'popup_container ' + style}>
      <div className="popup_window description">
        <div className="catbg popup_heading">
          {header}
          <a onClick={handleClose} title={smfTextVars.general.close} >{String.fromCodePoint(10060)}</a>
        </div>
        <div className="popup_content">
          {body}
        </div>
      </div>
    </div>
  )
}

export default Modal
