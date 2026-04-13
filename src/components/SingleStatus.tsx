import type { PermissionsContextType } from "breezeTypesPermissions";
import type { IFetchStatus, StatusType } from "breezeTypesStatus";
import type React from "react";
import { useCallback, useEffect, useState } from "react";
import { Toaster } from "react-hot-toast";

import { deleteStatus } from "../api/Status/Delete";
import { getSingleStatus } from "../api/Status/GetSingle";
import { PermissionsContext } from "../context/PermissionsContext";
import PermissionsDefault from "../DataSource/Permissions";
import smfVars from "../DataSource/SMF";
import smfTextVars from "../DataSource/Txt";
import { displayMessage } from "../utils/tooltip";
import Loading from "./Loading";
import Status from "./Status";

interface SingleStatusProps {
	statusId: number;
}

export default function SingleStatus(
	props: SingleStatusProps,
): React.JSX.Element {
	const [status, setStatus] = useState<StatusType | null>(null);
	const [isLoading, setIsLoading] = useState(true);
	const [notFound, setNotFound] = useState(false);
	const [permissions, setPermissions] =
		useState<PermissionsContextType>(PermissionsDefault);

	useEffect(() => {
		if (!props.statusId || props.statusId === 0) {
			setNotFound(true);
			setIsLoading(false);
			return;
		}

		getSingleStatus(props.statusId)
			.then((statusResponse: IFetchStatus | undefined) => {
				if (
					!statusResponse ||
					!statusResponse.data ||
					statusResponse.data.length === 0
				) {
					setNotFound(true);
					return;
				}

				const fetchedStatus: StatusType = statusResponse.data[0];
				setStatus(fetchedStatus);
				setPermissions(statusResponse.permissions);
			})
			.catch(() => {
				setNotFound(true);
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, [props.statusId]);

	const removeStatus = useCallback((currentStatus: StatusType) => {
		setIsLoading(true);

		deleteStatus(currentStatus.id)
			.then((deleted: boolean) => {
				if (deleted) {
					// Redirect to wall after deletion
					window.location.href = `${smfVars.script_url}?action=wall`;
				}
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, []);

	const goBack = () => {
		window.history.back();
	};

	if (isLoading) {
		return <Loading />;
	}

	if (notFound || !status) {
		return (
			<>
				<Toaster
					toastOptions={{
						duration: 4000,
					}}
				/>
				<div className="windowbg">
					<div className="content">
						{displayMessage(smfTextVars.error.generic)}
					</div>
					<div id="post_confirm_buttons">
						<input
							type="button"
							value={smfTextVars.general.goBack || "Go Back"}
							className="button"
							onClick={goBack}
						/>
					</div>
				</div>
			</>
		);
	}

	return (
		<>
			<Toaster
				toastOptions={{
					duration: 4000,
				}}
			/>
			<PermissionsContext.Provider value={permissions}>
				<div id="post_confirm_buttons" style={{ marginBottom: "10px" }}>
					<input
						type="button"
						value={smfTextVars.general.goBack || "Go Back"}
						className="button"
						onClick={goBack}
					/>
				</div>
				<ul className="status">
					<Status key={status.id} status={status} removeStatus={removeStatus} />
				</ul>
				<div id="post_confirm_buttons">
					<input
						type="button"
						value={smfTextVars.general.goBack || "Go Back"}
						className="button"
						onClick={goBack}
					/>
				</div>
			</PermissionsContext.Provider>
			{isLoading ? <Loading /> : ""}
		</>
	);
}
