import { Component } from 'react'

declare module 'breezeTypes' {
  interface modalProps {
    isShowing: boolean
    header: string
    body: any
  }
}

module.exports = {
  appProps
}
