import React from 'react'
import ReactDOM from 'react-dom'
import { modalProps } from 'breezeTypes'

const Modal: React.FunctionComponent<modalProps> = ({ isShowing, header, body }: modalProps) => {
  return isShowing
    ? ReactDOM.createPortal(
      <React.Fragment>
        {header}
        {body}
      </React.Fragment>, document.body
    )
    : null
}

export default Modal
