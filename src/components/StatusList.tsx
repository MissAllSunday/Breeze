import { statusType, StatusListProps } from 'breezeTypes'
import Status from './Status'
import React from 'react'

export const StatusList = ({
  statusList,
  onRemoveStatus,
  removeComment,
  onCreateComment
}: StatusListProps): React.ReactElement<Status> => (
  <ul className="status">
    {statusList.map((status: statusType) => (
      <Status
        key={status.id}
        status={status}
        removeStatus={onRemoveStatus}
        removeComment={removeComment}
        createComment={onCreateComment}
      />
    ))}
  </ul>
)
