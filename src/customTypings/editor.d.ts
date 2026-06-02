declare module "breezeTypesEditor" {
	interface EditorProps {
		saveContent: (content: string, mentionIds?: number[]) => boolean;
		isFull: boolean;
	}
}
