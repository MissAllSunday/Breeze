import * as React from 'react'
import { StatusProps } from 'breezeTypes'
import Like from './Like'
import CommentList from './CommentList'

export default class Status extends React.Component<StatusProps> {
  constructor (props: any) {
    super(props)
  }

  render () {
    return <li key={this.props.status.id}>
	<div className='breeze_avatar avatar_status floatleft'>
		<div className='windowbg'>
			<h4 className='floatleft'>
				h4 heading
			</h4>
			<div className='floatright smalltext'>
				{this.props.status.createdAt}
				&nbsp;<span className="main_icons remove_button floatright pointer_cursor" onClick={() => this.props.removeStatus(this.props.status)}>delete</span>
			</div>
			<br />
				<div className='content'>
					<hr />
					{this.props.status.body}
					<Like
						item={this.props.status.likesInfo}
					/>
				</div>
				<CommentList
					comments={this.props.status.comments}
				/>
			<div className='comment_posting'>
				editor component here
			</div>
		</div>
	</div>
</li>
  }
}
