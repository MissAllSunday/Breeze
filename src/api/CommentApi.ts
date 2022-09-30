import axios from "axios";
import { baseUrl, baseConfig } from "./Api";
import { statusType } from 'breezeTypes';
import smfVars from "../DataSource/SMF";

export interface ServerStatusResponse {
	data: ServerCommentData
}

interface ServerCommentData {
	users: object
	status: Array<statusType>
}

const action = 'breezeComment';

export const postComment = (commentParams:object) =>
{
	return axios.post<ServerCommentData>(
		baseUrl(action, 'postComment', [{
			...commentParams,
			userId: smfVars.userId,
		}]),
		baseConfig(),
	)
}

export const deleteComment = (commentId:number) =>
{
	// SMF cannot handle custom methods without changing some settings, thus, we are going to use POST for delete calls
	return axios.post(baseUrl(action, 'deleteComment'), baseConfig({
		id: commentId,
		userId: smfVars.userId
	}));
}

