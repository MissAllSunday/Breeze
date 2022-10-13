import React, { Component } from 'react'
import { moodType, MoodState, MoodProps } from 'breezeTypes'
import SMF from '../DataSource/SMF'
import smfTextVars from '../DataSource/Txt'
import Modal from './modal/Modal'
import MoodList from './MoodList.t'

export default class Mood extends Component<MoodProps, MoodState> {
  constructor (props: MoodProps) {
    super(props)

    this.state = {
      currentMood: this.props.mood,
      isShowing: false
    }
  }

  displayMood () {
    const moodTextVars = smfTextVars.mood

    return this.props.mood ? this.props.mood.emoji : moodTextVars.moodChange
  }

  handleMoodModification () {
    const smfVars = SMF
    const moodText = this.displayMood()

    if (smfVars.isCurrentUserOwner && smfVars.useMood) {
      return <span onClick={this.showMoodList} title="{this.props.moodTxt.defaultLabel}" className="pointer_cursor">
				{moodText}</span>
    } else {
      return { moodText }
    }
  }

  showMoodList = () => {
    this.setState({
      isShowing: true
    })
  }

  onChangeMood (mood: moodType) {
    this.setState(
      (prevState) => {
        return {
          currentMood: mood
        }
      },
      () => console.log('call the server to save the change', this.state.currentMood)
    )
  }

  render () {
    return <div>
			{/* <Modal */}
			{/*	isShowing={this.state.isShowing} */}
			{/*	body={<MoodList/>} */}
			{/*	header='some header' */}
			{/* /> */}
			{/* {this.handleMoodModification() } */}
	</div>
  }
}
