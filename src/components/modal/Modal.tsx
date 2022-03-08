import React from 'react';
import ReactDOM from 'react-dom';
import { modalProps } from 'breezeTypes';

const Modal = ({ isShowing, header, body } : modalProps) => isShowing ? ReactDOM.createPortal(
	<React.Fragment>
		{header}
		{body }
</React.Fragment>, document.body
) : null;

export default Modal;
