import React from 'react'

const Emoji: React.FunctionComponent<any> = (props: any) => {
  return <span
    className="emoji"
    role="img"
    aria-label={props.mood.description !== '' ? props.mood.description : ''}
    aria-hidden={props.mood.description === '' ? 'false' : 'true'}
    onClick={props.handleClick(props.mood)}
  >
  {String.fromCodePoint(props.mood.emoji)}
  </span>
}

export default Emoji
