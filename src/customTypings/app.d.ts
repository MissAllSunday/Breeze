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

  interface appProps {
    smfVars: smfVars
  }
}

module.exports = {
  smfVars,
  appProps
}
