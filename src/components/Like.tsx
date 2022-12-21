import React from 'react'
import { ServerLikeResponse, postLike } from '../api/LikeApi'
import { LikeProps } from 'breezeTypes'

export default class Like extends React.Component<LikeProps> {
  handleLike = (): void => {
    postLike(this.props.item).then((response: ServerLikeResponse) => {
      this.setState(response.data)
    }).catch(exception => {
    })
  }

  render (): JSX.Element {
    const like = this.props.item.alreadyLiked ? 128078 : 128077
    return <div className="smflikebutton">
    <span onClick={this.handleLike} className='likeClass' >
      {String.fromCodePoint(like)}
    </span>
  <div className="like_count smalltext">
    {this.props.item.additionalInfo.text}
  </div>
</div>
  }
}
