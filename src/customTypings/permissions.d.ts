declare module 'breezeTypesPermissions' {
  type PermissionsType = {
    edit: boolean,
    delete: boolean,
    post: boolean,
  };

  type ForumPermissionsType = {
    likesLike : boolean,
    adminForum: boolean,
    profileView: boolean,
  };

  type IsEnableType = {
    enableLikes: boolean,
  };

  type PermissionsContextType = {
    Status: PermissionsType,
    Comments: PermissionsType,
    isEnable: IsEnableType,
    Forum: ForumPermissionsType,
  };

}

module.exports = {
  PermissionsContextType,
};
