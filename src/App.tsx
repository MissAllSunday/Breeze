import React from 'react'
import UserInfo from './components/user/UserInfo'
import StatusByProfile from './components/StatusByProfile'

function App () {
  const currentUserData: object = {}

  return (
    <div className="App breeze_main_section">
      <UserInfo {...currentUserData}/>
      <div className="breeze_wall floatright">
        <ul className="status">
          <StatusByProfile />
        </ul>
      </div>
    </div>
  )
}

export default App
