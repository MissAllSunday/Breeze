export const baseConfig = (params: object = {}): object => ({
  data: params,
  headers: {
    'X-SMF-AJAX': '1',
  },
});





