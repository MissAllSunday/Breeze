import React from 'react';
import './App.css';
import UserInfo from "./components/user/UserInfo";
import Editor from "./components/Editor";
import Status from "./components/Status";
import { appProps } from 'breezeTypes';

function App(props:appProps) {
	let currentUserData: object = {}
	let statusByUser: Status[] = []
	let users = {}
	let listStatus = statusByUser.map((status: {status_id: ''}) =>
		<Status
			key={status.status_id}
			status={status}
			users={users}
			removeStatus={onRemoveStatus}
			setNewUsers={onSetNewUsers}
		/>
	)


  return (
    <div className="App breeze_main_section">
		<UserInfo {...currentUserData}/>
		<div className="breeze_wall floatright">
			<Editor />
			<ul className="status">
				{listStatus}
			</ul>
		</div>
    </div>
  );
}

function onRemoveStatus(status: object): void{
	console.log(status)
}

function onSetNewUsers(user: object): void{
	console.log(user)
}

export default App;
