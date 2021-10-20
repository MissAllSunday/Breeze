import axios from "axios";
import React, { useState, useEffect } from 'react';
import Comment from "../components/Comment";

export default function  commentsByStatusId(): JSX.Element{
	const [commentData, setCommentData] = useState([]);
	const [fetching, setFetching] = useState(false);

	useEffect(() => {
		setFetching(true);
		axios.get("https://domain.in/api/employees")
			.then(response => {
				this.setState({employees: response.data, isFetching:  false})
			})
			.catch(exception => {
				console.log(exception);
				this.setState({...this.state, isFetching: false});
			});
	});

	return (<Comment/>);
};

