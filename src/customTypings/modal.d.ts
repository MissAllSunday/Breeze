declare module 'breezeTypes' {
  interface modalProps {
    show: boolean
    content: {
      header: string | null
      body: JSX | null
    }
    onClose: function
  }
}

module.exports = {
  appProps
}
