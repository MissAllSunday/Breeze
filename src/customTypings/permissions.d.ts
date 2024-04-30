declare module 'breezeTypesPermissions' {
  type PermissionsType = {
    edit: boolean,
    delete: boolean,
    post: boolean,
  };

  type PermissionsContextType = {
    Status: PermissionsType,
    Comments: PermissionsType,
  };

}

module.exports = {
  PermissionsContextType,
  PermissionsDefault,
};
