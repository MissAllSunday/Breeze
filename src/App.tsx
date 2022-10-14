import React from 'react'
import StatusByProfile from './components/StatusByProfile'

function App (): JSX.Element {
  return (
    <div className="App breeze_main_section">
      {/* user info component */}
      <div className="breeze_wall floatright">
        <ul className="status">
          <StatusByProfile />
        </ul>
      </div>
    </div>
  )
}

export default App
