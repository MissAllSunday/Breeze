import axios, { AxiosResponse } from 'axios'
import { baseConfig, baseUrl } from './Api'
import { moodType } from 'breezeTypes'

const action = 'breezeMood'

export interface ServerMoodResponse {
  data: moodType[]
}

export const getActiveMoods = async (): Promise<AxiosResponse<ServerMoodResponse>> => {
  return await axios.get<ServerMoodResponse>(
    baseUrl(action, 'getActiveMoods'),
    baseConfig()
  )
}
