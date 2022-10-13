declare module 'breezeTypes' {
  interface moodType {
    id: number
    emoji: number
    body: string
    description: string
    isActive: boolean
  }

  interface MoodProps {
    mood: moodType
  }

  interface MoodState {
    currentMood: moodType
    isShowing: boolean
  }
}

module.exports = {
  moodType,
  MoodProps,
  MoodState
}
