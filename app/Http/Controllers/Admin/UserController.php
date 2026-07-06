<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('email', 'ilike', '%'.$request->q.'%')
                    ->orWhere('nom', 'ilike', '%'.$request->q.'%')
                    ->orWhere('prenom', 'ilike', '%'.$request->q.'%');
            }))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('actif'), fn ($q) => $q->where('actif', $request->boolean('actif')))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['actif' => true, 'role' => UserRole::Agent]),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->except('password'),
            'password' => $request->validated('password'),
            'actif' => $request->boolean('actif', true),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        if ($user->id === $request->user()->id) {
            $data['actif'] = true;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        if ($user->role === UserRole::Admin && User::query()->where('role', UserRole::Admin)->where('actif', true)->count() <= 1) {
            return back()->with('error', 'Impossible de désactiver le dernier administrateur actif.');
        }

        $user->update(['actif' => false]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur désactivé.');
    }
}
