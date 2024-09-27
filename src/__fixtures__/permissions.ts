import { PermissionsContextType } from 'breezeTypesPermissions';

import PermissionsDefault from '../DataSource/Permissions';


const basic:PermissionsContextType = PermissionsDefault;

const custom = (replace: Partial<PermissionsContextType>) => {
  return { ...basic, ...replace };
};

const permissions = { basic, custom };

export default  permissions;
