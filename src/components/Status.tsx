import * as React from "react";
import {StatusState, StatusProps } from 'breezeTypes';
import Like from "./Like";


export default class Status extends React.Component<StatusProps, StatusState>
{
	render() {
		return <li>
	<div className='breeze_avatar avatar_status floatleft'>
		<div className='windowbg'>
			<h4 className='floatleft'>
				h4 heading
			</h4>
			<div className='floatright smalltext'>
				{this.props.status.createdAt}
				&nbsp;<span className="main_icons remove_button floatright pointer_cursor" onClick={() =>
				this.props.removeStatus(this.props.status)}/>
			</div>
			<br />
				<div className='content'>
					<hr />
					{this.props.status.body}
					<Like
						item={this.props.status.likesInfo}
					/>
				</div>
				comment component here
			<div className='comment_posting'>
				editor component here
			</div>
		</div>
	</div>
</li>;
	}
}
