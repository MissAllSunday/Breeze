declare module 'breezeTypes' {
	type statusType = {
		id: number,
		wallId: number,
		userId: number,
		likes: number,
		body: string,
		createdAt: string
	}
}

module.exports = {
	statusType,
};
