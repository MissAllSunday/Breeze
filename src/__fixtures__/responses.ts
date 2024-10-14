const basic = new Response();

const custom = (replace: Partial<Response>) => {
  return { ...basic, ...replace };
};

export const responses = { basic, custom };
