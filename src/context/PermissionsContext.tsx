import { PermissionsContextType } from 'breezeTypesPermissions';
import { createContext } from 'react';

import PermissionsDefault from '../DataSource/Permissions';

export const PermissionsContext = createContext<PermissionsContextType>(PermissionsDefault);
