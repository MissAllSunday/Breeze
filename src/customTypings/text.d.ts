declare module 'breezeTypes' {
  interface moodTextType {
    emoji: string
    description: string
    enable: string
    invalidEmoji: string
    emptyEmoji: string
    moodChange: string
    newMood: string
    sameMood: string
    defaultLabel: string
  }

  interface generalTextType {
    save: string, delete: string, editing: string, close: string, cancel: string, send: string, preview: string, previewing: string, wrongValues: string, errorEmpty: string
  }
  interface likeTextType {
    unlike: string
    like: string
  }
}

module.exports = {
  moodTextType,
  generalTextType,
  likeTextType
}
