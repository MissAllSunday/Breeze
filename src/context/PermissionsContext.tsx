import { PermissionsContextType } from 'breezeTypes';
import { createContext } from 'react';

export const PermissionsContext = createContext<PermissionsContextType>({
  Status: {
    edit: false,
    delete: false,
    post: false,
  },
  Comments: {
    edit: false,
    delete: false,
    post: false,
  },
});
