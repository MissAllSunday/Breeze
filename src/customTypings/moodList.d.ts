import Mood from '../components/Mood'

declare module 'breezeTypes' {
  interface MoodListState {
    moodList: Mood[]
    isShowing: boolean
  }
}

module.exports = {
  MoodListState
}
