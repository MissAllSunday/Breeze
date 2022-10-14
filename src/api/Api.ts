import SmfVars from '../DataSource/SMF'

export const baseUrl = (action: string, subAction: string, additionalParams: Object[] = []): string => {
  const baseUrl = new URL(SmfVars.scriptUrl)

  baseUrl.searchParams.append('action', action)
  baseUrl.searchParams.append('sa', subAction)
  baseUrl.searchParams.append(SmfVars.session.var, SmfVars.session.id)

  additionalParams.map((objectValue): null => {
    for (const [key, value] of Object.entries(objectValue)) {
      baseUrl.searchParams.append(key, value)
    }

    return null
  })

  return baseUrl.href
}

export const baseConfig = (params: object = {}): object => {
  return {
    data: params,
    headers: {
      'X-SMF-AJAX': '1'
    }
  }
}
