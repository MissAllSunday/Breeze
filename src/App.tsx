import React from 'react';
import './App.css';
import UserInfo from "./components/user/UserInfo";
import Editor from "./components/Editor";
import Status from "./components/Status";
import statusByUser from "./DataSource/StatusByUser";

function App() {
	let currentUserData: object = {}
	let statusByUserData:Status[] = statusByUser()

  return (
    <div className="App breeze_main_section">
		<UserInfo {...currentUserData}/>
		<div className="breeze_wall floatright">
			<Editor />
			<ul className="status">
				{statusByUserData}
			</ul>
		</div>
    </div>
  );
}

export default App;
