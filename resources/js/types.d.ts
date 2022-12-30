import { AxiosStatic } from "axios";
import { LoDashStatic } from "lodash";
import type { ErrorBag, Errors, Page, PageProps } from "@inertiajs/inertia";

export {};

declare global {
    interface Window {
        _: LoDashStatic;
        axios: AxiosStatic;
    }

    interface InertiaPage extends Page<PageProps> {
        props: {
            errors: Errors & ErrorBag;
            auth: {
                user: {
                    name: string;
                    email: string;
                    email_verified_at: string;
                };
            };
        };
    }
}
