declare module 'breezeTypes' {
  interface smfVarsType {
    session: {
      var: string
      id: string
    }
    youSure: string
    ajaxIndicator: boolean
    txt: {}
    scriptUrl: string
    userId: number
  }

  interface wallProps {
    wallType: string
  }

  interface wallProps {
    wallType: string
  }
}

module.exports = {
  smfVars,
  wallProps
}
