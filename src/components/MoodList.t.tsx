import React from 'react'
import { getActiveMoods } from '../api/MoodApi'
import { moodType } from 'breezeTypes'

export default class MoodList extends React.Component<any, any> {
  constructor (props: {}) {
    super(props)
    this.state = {
      list: []
    }
  }

  saveMood (mood: moodType): void {

  }

  componentDidMount (): void {
    // const moods: moodType[] = []

    getActiveMoods().then((result) => {

    }).catch(exception => {
    })
  }

  render (): JSX.Element {
    return <div id="moodList">
      <ul>{this.state.list}</ul>
    </div>
  }
}
