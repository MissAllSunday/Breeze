declare module 'breezeTypes' {
  interface smfVarsType {
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

  interface wallState {
    list: statusType[]
    isLoading: boolean
  }

  interface wallProps {
    wallType: string
    pagination: number
  }
}

module.exports = {
  smfVars,
  wallProps,
  wallState
}
