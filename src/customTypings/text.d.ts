declare module 'breezeTypesText' {

  interface GeneralTextType {
    deletedStatus: string,
    deletedComment: string,
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

  interface ErrorTextType {
    wrongValues: string,
    errorEmpty: string,
    noStatus: string,
    generic: string,
  }
}

module.exports = {
  TabsTextType,
  GeneralTextType,
  LikeTextType,
  ErrorTextType,
};
