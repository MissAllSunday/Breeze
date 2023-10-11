declare module 'breezeTypes' {
  interface ModalProps {
    show: boolean
    content: {
      header: string | null
      body: JSX | null
    }
    onClose: function
  }
}

module.exports = {
  appProps,
};
