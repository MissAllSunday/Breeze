import {ServerStatusResponse, getByProfile, deleteStatus, postStatus, ServerPostStatusResponse} from "../api/StatusApi";
import React, { useState, useEffect } from 'react';
import Status from "./Status";
import { statusType } from 'breezeTypes';
import Loading from "./Loading";
import Editor from "./Editor";
import {AxiosResponse} from "axios";

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
		this.updateState({
			isLoading: true
		});

		let tmpStatusList:Array<any> = []
		const response = postStatus(content).then((response:AxiosResponse<ServerPostStatusResponse>) => {
			for (let [key, status] of Object.entries(response.data.content)) {
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
			if (response.status === 201) {
				console.log(response.status)
			} else {
				// show some error
			}

		}).catch(function (error) {
			if (error.response) {
				console.log(error.response.data);
				console.log(error.response.status);
				console.log(error.response.headers);
			}
		}).finally(() => {
			this.updateState({
				isLoading: false
			});
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
