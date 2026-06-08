<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\ActivityLog;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\CustomerReferenceGenerator;
use App\Support\WhatsappLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->hasAdminPermission('view_customers'), 403);

        $search = $request->string('search')->trim()->value();

        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->with('customerProfile')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('customerProfile', function ($profile) use ($search) {
                            $profile->where('customer_reference', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'customer_reference' => $user->customerProfile?->customer_reference,
                'phone' => $user->customerProfile?->phone,
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/customers/Index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
            'canManageCustomers' => $request->user()?->hasAdminPermission('manage_customers'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->hasAdminPermission('manage_customers'), 403);

        return Inertia::render('admin/customers/Create');
    }

    public function store(
        StoreCustomerRequest $request,
        CustomerReferenceGenerator $references,
    ): RedirectResponse {
        abort_unless($request->user()?->hasAdminPermission('manage_customers'), 403);

        $user = DB::transaction(function () use ($request, $references): User {
            $user = User::create([
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'password' => Hash::make($request->string('password')->value()),
                'role' => User::ROLE_CUSTOMER,
                'status' => $request->string('status')->value(),
                'email_verified_at' => now(),
            ]);

            CustomerProfile::create([
                'user_id' => $user->id,
                'customer_reference' => $references->next(),
                'trn' => $request->input('trn'),
                'phone' => $request->input('phone'),
                'whatsapp_number' => $request->input('whatsapp_number'),
                'jamaica_address' => $request->input('jamaica_address'),
                'parish' => $request->input('parish'),
                'date_of_birth' => $request->input('date_of_birth'),
            ]);

            return $user;
        });

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_UPDATED,
            "{$request->user()->name} created customer {$user->name}.",
            $request->user(),
            $user,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Customer created.']);

        return to_route('admin.customers.index');
    }

    public function show(Request $request, User $customer): Response
    {
        abort_unless($request->user()?->hasAdminPermission('view_customers'), 403);
        abort_unless($customer->isCustomer(), 404);

        $customer->load([
            'customerProfile.authorisedPickupPerson',
            'preAlerts' => fn ($q) => $q->latest()->limit(10),
            'packages' => fn ($q) => $q->latest()->limit(10),
        ]);

        return Inertia::render('admin/customers/Show', [
            'customer' => $this->customerPayload($customer),
            'preAlerts' => $customer->preAlerts->map(fn ($pa) => [
                'id' => $pa->id,
                'merchant_name' => $pa->merchant_name,
                'status_label' => $pa->status->label(),
                'created_at' => $pa->created_at?->toIso8601String(),
            ]),
            'packages' => $customer->packages->map(fn ($pkg) => [
                'id' => $pkg->id,
                'package_reference' => $pkg->package_reference,
                'status_label' => $pkg->status->label(),
                'amount_due' => (float) $pkg->amount_due,
            ]),
            'canManageCustomers' => $request->user()?->hasAdminPermission('manage_customers'),
        ]);
    }

    public function edit(Request $request, User $customer): Response
    {
        abort_unless($request->user()?->hasAdminPermission('manage_customers'), 403);
        abort_unless($customer->isCustomer(), 404);

        $customer->load('customerProfile');

        return Inertia::render('admin/customers/Edit', [
            'customer' => $this->customerPayload($customer),
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        abort_unless($request->user()?->hasAdminPermission('manage_customers'), 403);
        abort_unless($customer->isCustomer(), 404);

        $customer->fill($request->only(['name', 'email', 'status']));
        $customer->save();

        $customer->customerProfile()->updateOrCreate(
            ['user_id' => $customer->id],
            $request->only([
                'trn',
                'phone',
                'whatsapp_number',
                'jamaica_address',
                'parish',
                'date_of_birth',
            ]),
        );

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_UPDATED,
            "{$request->user()->name} updated customer {$customer->name}.",
            $request->user(),
            $customer,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Customer updated.']);

        return to_route('admin.customers.show', ['customer' => $customer]);
    }

    /**
     * @return array<string, mixed>
     */
    private function customerPayload(User $customer): array
    {
        $pickupPerson = $customer->customerProfile?->authorisedPickupPerson;

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'status' => $customer->status,
            'customer_reference' => $customer->customerProfile?->customer_reference,
            'trn' => $customer->customerProfile?->trn,
            'phone' => $customer->customerProfile?->phone,
            'whatsapp_number' => $customer->customerProfile?->whatsapp_number,
            'jamaica_address' => $customer->customerProfile?->jamaica_address,
            'parish' => $customer->customerProfile?->parish,
            'date_of_birth' => $customer->customerProfile?->date_of_birth?->toDateString(),
            'created_at' => $customer->created_at?->toIso8601String(),
            'authorised_pickup_person' => $pickupPerson ? [
                'full_name' => $pickupPerson->full_name,
                'phone' => $pickupPerson->phone,
                'relationship_note' => $pickupPerson->relationship_note,
                'id_number' => $pickupPerson->id_number,
            ] : null,
            'whatsapp_url' => WhatsappLink::forPhone(
                $customer->customerProfile?->whatsapp_number
                    ?? $customer->customerProfile?->phone,
                'Hi '.$customer->name.", this is Ship'd JM support.",
            ),
        ];
    }
}
