declare module 'breezeTypesPermissions' {
  type PermissionsType = {
    edit: boolean,
    delete: boolean,
    post: boolean,
  };

  type IsEnableType = {
    enableLikes: boolean,
  };

  type PermissionsContextType = {
    Status: PermissionsType,
    Comments: PermissionsType,
    IsEnable: IsEnableType
  };

}

module.exports = {
  PermissionsContextType,
};
