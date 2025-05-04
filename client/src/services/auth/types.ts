import z from "zod";

export const loginRequestSchema = z.object({
    email: z.string(),
    password: z.string()
});

export type LoginRequest = z.infer(typeof loginRequestSchema);