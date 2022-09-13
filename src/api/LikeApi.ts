import axios from "axios";
import { baseUrl, baseParams } from "./Api";
import { likeType } from 'breezeTypes';

export interface ServerLikeResponse {
	data: ServerLikeData
}

export interface ServerLikeData {
	content: Array<likeType>
}

const action = 'like';

export const postLike = (likeData:object) =>
{
	return axios.post<ServerLikeData>(
		baseUrl(action, 'like'),
		{ data: baseParams(likeData)},
	)
}

