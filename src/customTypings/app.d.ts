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

  interface TabContentProps {
    content: string,
    name: string
  }

  interface WallState {
    list: StatusType[]
    isLoading: boolean
  }

  interface WallProps {
    wallType: string,
    pagination: number,
    name: string,
  }
}

module.exports = {
  TabContentProps,
  smfVars,
  WallProps,
  WallState,
};
