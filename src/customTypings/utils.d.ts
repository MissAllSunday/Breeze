declare module 'breezeTypes' {

	interface UtilsProps {
		smfVars: smfVars
	}

	interface ToastCallback {
		(): void;
	}

	interface NoticeOptions {
		message: string
		appearance: string,
		autoDismiss: boolean,
	}
}

module.exports = {
	UtilsProps,
	ToastCallback,
	NoticeOptions
};
