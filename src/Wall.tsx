import type { WallProps } from "breezeTypes";
import type { PermissionsContextType } from "breezeTypesPermissions";
import type {
	IFetchStatus,
	StatusListType,
	StatusType,
} from "breezeTypesStatus";
import React, { useCallback, useEffect, useState } from "react";
import { Toaster } from "react-hot-toast";

import { deleteStatus } from "./api/Status/Delete";
import { getStatus } from "./api/Status/Get";
import { postStatus } from "./api/Status/Post";
import Editor from "./components/Editor";
import Loading from "./components/Loading";
import SingleStatus from "./components/SingleStatus";
import Status from "./components/Status";
import { PermissionsContext } from "./context/PermissionsContext";
import PermissionsDefault from "./DataSource/Permissions";
import smfTextVars from "./DataSource/Txt";
import { displayMessage, showInfo } from "./utils/tooltip";

export default function Wall(props: WallProps): React.JSX.Element {
	// If statusId is provided, render SingleStatus component
	// This avoids unnecessary hook initialization for the feed view
	if (props.statusId && props.statusId > 0) {
		return <SingleStatus statusId={props.statusId} />;
	}

	return <WallFeed {...props} />;
}

function WallFeed(props: WallProps): React.JSX.Element {
	const [statusList, setStatusList] = useState<StatusListType>([]);
	const [isLoading, setIsLoading] = useState(true);
	const [emptyData, setEmptyData] = useState(false);
	const [permissions, setPermissions] =
		useState<PermissionsContextType>(PermissionsDefault);
	const [nextCursor, setNextCursor] = useState<string | null>(null);
	const [hasMore, setHasMore] = useState<boolean>(false);
	const ref = React.useRef<null | HTMLInputElement>(null);

	useEffect(() => {
		if (ref.current) {
			ref.current.scrollIntoView({ behavior: "smooth", block: "end" });
		}
	}, []);

	useEffect(() => {
		getStatus(props.wallType, 0, null)
			.then((statusListResponse: IFetchStatus | undefined) => {
				if (!statusListResponse) {
					return;
				}

				const fetchedStatusList: StatusListType = Object.values(
					statusListResponse.data,
				);
				setEmptyData(statusListResponse.data.length === 0);
				setStatusList(fetchedStatusList);
				setPermissions(statusListResponse.permissions);
				setNextCursor(statusListResponse.pagination.nextCursor);
				setHasMore(statusListResponse.pagination.hasMore);
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, [props.wallType]);

	const fetchNextStatus = useCallback(() => {
		if (!hasMore) {
			showInfo(smfTextVars.general.end);
			return;
		}

		setIsLoading(true);

		getStatus(props.wallType, statusList.length, nextCursor)
			.then((statusListResponse: IFetchStatus | undefined) => {
				if (!statusListResponse) {
					return;
				}

				setStatusList((prevStatusList: StatusListType) =>
					prevStatusList.concat(Object.values(statusListResponse.data)),
				);
				setNextCursor(statusListResponse.pagination.nextCursor);
				setHasMore(statusListResponse.pagination.hasMore);
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, [props.wallType, statusList.length, nextCursor, hasMore]);

	const createStatus = useCallback((content: string) => {
		setIsLoading(true);

		postStatus(content)
			.then((newStatus: StatusListType) => {
				setStatusList((prevStatusList: StatusListType) => [
					...prevStatusList,
					...Object.values(newStatus),
				]);
			})
			.finally(() => {
				setIsLoading(false);
			});

		return true;
	}, []);

	const removeStatus = useCallback((currentStatus: StatusType) => {
		setIsLoading(true);

		deleteStatus(currentStatus.id)
			.then((deleted: boolean) => {
				if (deleted) {
					setStatusList((prevStatusList: StatusListType) =>
						prevStatusList.filter(
							(status: StatusType) => currentStatus.id !== status.id,
						),
					);
				}
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, []);

	const goUp = () => {
		window.scrollTo({ top: 0, behavior: "smooth" });
	};

	return (
		<>
			{permissions.Status.post ? (
				<Editor saveContent={createStatus} isFull={true} />
			) : (
				""
			)}
			<Toaster
				toastOptions={{
					duration: 4000,
				}}
			/>
			<PermissionsContext.Provider value={permissions}>
				<ul className="status">
					{!emptyData
						? statusList.map((singleStatus: StatusType) => (
								<Status
									key={singleStatus.id}
									status={singleStatus}
									removeStatus={removeStatus}
								/>
							))
						: displayMessage(smfTextVars.general.emptyData)}
				</ul>
				<div id="post_confirm_buttons">
					{hasMore ? (
						<input
							type="submit"
							value={smfTextVars.general.loadMore}
							name={smfTextVars.general.loadMore}
							className="button"
							onClick={fetchNextStatus}
							ref={ref as React.LegacyRef<HTMLInputElement>}
						/>
					) : (
						""
					)}
					<input
						type="submit"
						value={smfTextVars.general.goUp}
						name={smfTextVars.general.goUp}
						className="button"
						onClick={goUp}
					/>
				</div>
			</PermissionsContext.Provider>
			{isLoading ? <Loading /> : ""}
		</>
	);
}
