ajax_indicator(true);

new Vue({
	el: '#',
//	mixins: [breezeUtils],
	data: {
		txt:  window.breezeTxtGeneral,
		txtMood:  window.breezeTxtMood,
		errored: false,
		notice: null,
		users:  {},
		useMood: window.breezeUseMood
	},
	methods: {
	}
});
