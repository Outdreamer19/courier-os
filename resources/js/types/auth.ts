export type UserRole = 'admin' | 'customer';

export type UserStatus = 'active' | 'suspended';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    role: UserRole;
    status: UserStatus;
    is_admin: boolean;
    is_customer: boolean;
    customer_reference: string | null;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type WarehouseSnapshot = {
    name: string;
    address_line_1: string;
    address_line_2: string | null;
    city: string;
    state: string;
    zip: string;
    phone: string | null;
    instructions: string | null;
    single_line: string;
};

export type RateSnapshot = {
    name: string;
    currency: string;
    rate_per_lb: number;
    minimum_charge: number;
    handling_fee: number | null;
    min_weight_lbs: number | null;
    max_weight_lbs: number | null;
};

export type BrandConfig = {
    currency: string;
    default_rate_per_lb: number;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
