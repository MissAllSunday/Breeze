import React from 'react'
import '@testing-library/jest-dom'
import { render, screen } from '@testing-library/react'
import Emoji from './Emoji'
import { moodType } from 'breezeTypes'

const mood: moodType = {
  id: 1,
  emoji: 128512,
  body: '',
  description: '',
  isActive: true
}

const handleClickCalled = () => {
}

describe('App', () => {
  test('renders App component', () => {
    render(<Emoji mood={mood} handleClick={handleClickCalled} />)

    screen.debug()
    expect(screen.getByText('😀')).toBeInTheDocument()
    expect(screen.getByRole('img')).toBeInTheDocument()
  })
})
