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
    errorEmpty: string
    end: string
    loadMore: string
    goUp: string
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
