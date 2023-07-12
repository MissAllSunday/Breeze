import { StatusListProps, statusType } from 'breezeTypes'
import Status from './Status'
import React, { useCallback } from 'react'
import Editor from './Editor'

function StatusList (props: StatusListProps): React.ReactElement {
  const createStatus = useCallback((content: string) => {
    props.onCreate(content)
  }, [props])

  const removeStatus = useCallback((status: statusType) => {
    props.onDelete(status)
  }, [props])

  return (
    <div>
      <Editor saveContent={createStatus} />
      <ul className="status">
        {Object.keys(props.statusList).map((keyName, i) => (
          <Status
            key={props.statusList[i].id}
            status={props.statusList[i]}
            removeStatus={removeStatus}
          />
        ))}
          </ul>
    </div>
  )
}

export default React.memo(StatusList)
