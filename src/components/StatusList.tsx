import { statusType, StatusListProps } from 'breezeTypes'
import Status from './Status'
import React from 'react'

export const StatusList = ({
  statusList,
  removeStatus,
  removeComment,
  onCreateComment
}: StatusListProps): React.ReactElement => (
  <ul className="status">
    {statusList.map((status: statusType) => (
      <Status
        key={status.id}
        status={status}
        removeStatus={removeStatus}
        removeComment={removeComment}
        createComment={onCreateComment}
      />
    ))}
  </ul>
)
