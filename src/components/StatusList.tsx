import { statusType, StatusListProps } from 'breezeTypes'
import Status from './Status'
import React from 'react'

export const StatusList = ({
  statusList,
  onRemoveStatus,
  onRemoveComment,
  onCreateComment
}: StatusListProps): React.ReactElement<Status> => (
  <ul className="status">
    {statusList.map((status: statusType) => (
      <Status
        key={status.id}
        status={status}
        removeStatus={onRemoveStatus}
        removeComment={onRemoveComment}
        createComment={onCreateComment}
      />
    ))}
  </ul>
)
