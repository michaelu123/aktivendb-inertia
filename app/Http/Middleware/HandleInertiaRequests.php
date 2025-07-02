<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $sess = $request->session();
        $res = [
            ...parent::share($request),
            'flash' => [
                'success' => $request->session()->get('success')
            ],
            "user" => $user ? [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "is_admin" => $user->isAdmin,
                "may_read_history" => $user->mayReadHistory,
                //                "notificationCount" => $user->unreadNotifications()->count(),
            ] : null,
            "store" => $sess->get("store", []),
        ];
        return $res;
    }
}
