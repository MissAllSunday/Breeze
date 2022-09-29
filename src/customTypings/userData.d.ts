declare module 'breezeTypes' {
	type userDataType = {
		avatar: {
			href: string,
			image: HTMLImageElement,
			name: string,
			url: string,
		},
		buddies: Array,
		custom_fields: Array,
		email: string,
		group: string,
		group_color: string,
		group_icons: string,
		group_id: string,
		href: string,
		id: number,
		is_activated: string,
		is_banned: boolean,
		is_buddy: boolean,
		is_guest: boolean,
		is_reverse_buddy: boolean,
		last_login_timestamp: string,
		link: HTMLAnchorElement,
		link_color: HTMLAnchorElement,
		name: string,
		name_color: string,
		online: {
			href: string
			is_online: boolean
			label: string
			link: HTMLAnchorElement
			member_online_text: string
			text: string
		},
		signature: string,
		title: string,
		username: string,
		username_color: HTMLSpanElement,
	}
}

module.exports = {
	userDataType,
};
