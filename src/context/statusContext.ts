import { statusDispatchContextType } from 'breezeTypes'
import { createContext } from 'react'

export const StatusContext = createContext({})
export const StatusDispatchContext = createContext<statusDispatchContextType>(null)
