import type { CommentAction, StatusAction } from "breezeTypesActions";

interface Registry<T extends { id: string; order: number }> {
	register: (...items: T[]) => void;
	get: () => T[];
}

export function createRegistry<
	T extends { id: string; order: number },
>(): Registry<T> {
	const actions: T[] = [];

	const register = (...items: T[]): void => {
		for (const item of items) {
			if (!actions.some((a) => a.id === item.id)) {
				actions.push(item);
			}
		}
	};

	const get = (): T[] => [...actions].sort((a, b) => a.order - b.order);

	return { register, get };
}

export const statusActionRegistry = createRegistry<StatusAction>();
export const commentActionRegistry = createRegistry<CommentAction>();
