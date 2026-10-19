import type {
    Auth,
    BrandConfig,
    DemoConfig,
    WarehouseSnapshot,
} from '@/types/auth';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            brand: BrandConfig;
            warehouse: WarehouseSnapshot | null;
            demo: DemoConfig | null;
            /** True on the CourierOS platform domain, where no courier is bound. */
            is_platform?: boolean;
            flash: {
                success?: string | null;
                error?: string | null;
                status?: string | null;
            };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
