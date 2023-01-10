import React from 'react'
import smfVars from '../DataSource/SMF'

export default function Loading (): JSX.Element {
  const loadingImg = smfVars.smfImagesUrl + '/loading_sm.gif'
  return (
    <div className="centertext">
      <img src={loadingImg} />
    </div>
  )
}
