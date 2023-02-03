import React from 'react'
import { createRoot } from 'react-dom/client'
import Wall from './Wall'

const root = createRoot(document.getElementById('root') as HTMLElement)

root.render(
  <React.StrictMode>
    <Wall wallType="general"/>
  </React.StrictMode>
)
