declare module 'breezeTypes' {
  interface SmfVarsType {
    session: {
      var: string
      id: string
    }
    youSure: string
    ajaxIndicator: boolean
    txt: string[]
    scriptUrl: string
    userId: number
  }

  interface WallState {
    list: StatusType[]
    isLoading: boolean
  }

  interface WallProps {
    wallType: string,
    pagination: number,
    smfEditor: {
      instance: function
    }
  }

  type PermissionsContextType = {
    edit: boolean,
    delete: boolean,
    post: boolean,
    postComments: boolean
  } | null;
}

module.exports = {
  PermissionsContextType,
  smfVars,
  WallProps,
  WallState,
};
