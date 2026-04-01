export const env = {
  apiBaseUrl: process.env.EXPO_PUBLIC_API_BASE_URL ?? '',
  useMockApi: process.env.EXPO_PUBLIC_USE_MOCK_API === 'true',
};
