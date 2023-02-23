import { noticeProps } from 'breezeTypes'
import React from 'react'

export default function Notice (props: noticeProps): JSX.Element {
  return (
    <div className={props.type + 'box'}>
      <h3>{props.header}</h3>
      <div className="smalltext">{props.body}</div>
    </div>
  )
}

Notice.defaultProps = {
  type: 'notice',
  header: '',
  body: ''
}
