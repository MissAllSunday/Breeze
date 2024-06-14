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

  interface TabsTextType {
    wall: string,
    about: string,
    activity; string,
  }
}

module.exports = {
  TabsTextType,
  GeneralTextType,
  LikeTextType,
};
