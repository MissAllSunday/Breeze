import React from 'react'
import { ServerLikeResponse, postLike } from '../api/LikeApi'
import { LikeProps } from 'breezeTypes'

export default class Like extends React.Component<LikeProps> {
  handleLike (): void {
    postLike(this.props.item).then((response: ServerLikeResponse) => {
      this.setState(response.data)
    }).catch(exception => {
    })
  }

  render (): JSX.Element {
    return <div className="smflikebutton">
  <a onClick={this.handleLike} className="msg_like" href='/'>
    <span className='likeClass' />
    handle like
  </a>
  <div className="like_count smalltext">
    {this.props.item.count}
  </div>
</div>
  }
}
