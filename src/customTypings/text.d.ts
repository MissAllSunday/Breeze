declare module 'breezeTypesText' {

  interface GeneralTextType {
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

  interface LikeTextType {
    unlike: string
    like: string
  }
}

module.exports = {
  GeneralTextType,
  LikeTextType,
};
