import { PermissionsContextType } from 'breezeTypesPermissions';

const PermissionsDefault:PermissionsContextType = {
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
  IsEnable: {
    enableLikes: false,
  },
};

export default PermissionsDefault;
