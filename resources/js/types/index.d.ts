import "@inertiajs/core";

export interface FlashProps {
    message?: string | null;
    error?: string | null;
}

declare module "@inertiajs/core" {
    interface PageProps {
        auth: {
            user: {
                id: number;
                name: string;
                email: string;
            } | null;
        };
        flash: FlashProps;
    }
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
