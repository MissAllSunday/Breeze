declare module 'breezeTypes' {

  interface generalTextType {
    save: string
    delete: string
    editing: string
    close: string
    cancel: string
    send: string
    preview: string
    previewing: string
    wrongValues: string
    errorEmpty: string
  }

  interface likeTextType {
    unlike: string
    like: string
  }
}

module.exports = {
  generalTextType,
  likeTextType
}
