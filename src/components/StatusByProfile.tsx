import {ServerStatusResponse, getByProfile, deleteStatus, postStatus} from "../api/StatusApi";
import React, { useState, useEffect } from 'react';
import Status from "./Status";
import { statusType } from 'breezeTypes';
import Loading from "./Loading";
import Editor from "./Editor";

export default class StatusByProfile extends React.Component<any, any>
{
	constructor(props: any) {
		super(props);
		this.state = {
			list: {
				isLoading: true,
				data: []
			}
		};
	}

	updateState(newData:Object ) {
		let list = {...this.state.list, ...newData};
		this.setState({list});
	}

	componentDidMount(){
		let tmpStatusList:Array<any> = []

		getByProfile()
			.then((response:ServerStatusResponse) => {
				for (let [key, status] of Object.entries(response.data)) {
					tmpStatusList[status.id] = <Status
						key={status.id}
						status={status}
						users={this.state.usersData}
						removeStatus={this.onRemoveStatus}
					/>;
				}

				this.updateState({
					data: tmpStatusList,
					isLoading: false
				});


			})
			.catch(exception => {
			});
	}

	onRemoveStatus(status:statusType)
	{
		deleteStatus(status.id).then((response) => {
			console.log(response);
			return;

			if (response.status === 204) {
				let tmpStatusList = this.state.list.data;

				delete tmpStatusList[status.id];

				this.updateState({
					data: tmpStatusList,
				});
			} else {
				// show some error
			}

		}).catch(function (error) {
				if (error.response) {
					console.log(error.response.data);
					console.log(error.response.status);
					console.log(error.response.headers);
				}
			});
	}

	onNewStatus(content:string)
	{
		postStatus(content).then((response) => {
			console.log(response);

			if (response.status === 201) {

			} else {
				// show some error
			}

		}).catch(function (error) {
			if (error.response) {
				console.log(error.response.data);
				console.log(error.response.status);
				console.log(error.response.headers);
			}
		});
	}

	render(){
		return (
			this.state.isLoading ? <Loading /> : <ul className="status">
				{this.state.list.data}
				<Editor saveContent={this.onNewStatus} />
			</ul>
		)
	}
}
