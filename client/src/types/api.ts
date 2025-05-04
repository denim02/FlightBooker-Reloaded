export type Result<T> = {
  data: T;
  error: Error;
};

export type ApiResponse<T> = {
  data: T;
  message: string;
  status: number;
};
