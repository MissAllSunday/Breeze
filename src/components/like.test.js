import '@testing-library/jest-dom'

import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import React  from 'react';

import * as LikeApi from '../api/LikeApi'
import Like from './Like'

describe('Like', () => {
  it('has already been liked', async () => {
    const likeItem = {
      additionalInfo: {
        text: 'lol',
        href: 'lol'
      },
      alreadyLiked: false,
      canLike: true,
      contentId: 1,
      count: 0,
      type: 'lol'
    }
    window.confirm = () => { return true }
    const postLike = jest.spyOn(LikeApi, 'postLike').mockImplementation(() => Promise.resolve())

    const { getByTitle, debug } = render(<Like item={likeItem} />)
    const spanElement = getByTitle('lol')
    expect(spanElement).toBeInTheDocument()

    await waitFor(() => {
      userEvent.click(spanElement)
    })

    await waitFor(() => expect(spanElement).toHaveTextContent(String.fromCodePoint(128077)))
    await waitFor(() => expect(postLike).toHaveBeenCalledWith(likeItem))
  })
})
