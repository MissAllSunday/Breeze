import React from 'react';
import UserInfo from "./components/user/UserInfo";
import Editor from "./components/Editor";
import Status from "./components/Status";
import statusByUser from "./components/StatusByProfile";
import StatusByProfile from "./components/StatusByProfile";

function App() {
	let currentUserData: object = {}

  return (
    <div className="App breeze_main_section">
		<UserInfo {...currentUserData}/>
		<div className="breeze_wall floatright">
			<Editor />
			<ul className="status">
				<StatusByProfile />
			</ul>
		</div>
    </div>
  );
}

export default App;
