<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        // Check if the user has permission to view clients
        return $user->hasPermissionTo('client_view');
    }

    public function view(User $user, Client $client): bool
    {
        // the admin can view any client
        if ($user->hasPermissionTo('client_view_all')) {
            return true;
        }

        // the regular user can only view their own clients
        return $user->id === $client->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('client_create');
    }

    public function update(User $user, Client $client): bool
    {
        // the admin can edit any client
        if ($user->hasPermissionTo('client_view_all')) {
            return $user->hasPermissionTo('client_edit');
        }

        // the regular user can only edit their own clients
        return $user->id === $client->user_id && $user->hasPermissionTo('client_edit');
    }

    public function delete(User $user, Client $client): bool
    {
        // the admin can delete any client
        if ($user->hasPermissionTo('client_view_all')) {
            return $user->hasPermissionTo('client_delete');
        }

        // the regular user can only delete their own clients
        return $user->id === $client->user_id && $user->hasPermissionTo('client_delete');
    }
}
