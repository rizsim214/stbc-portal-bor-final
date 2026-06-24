<?php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}.appointments', fn(User $user, int $userId) => $user->id === $userId);
Broadcast::channel('admin.appointments', fn(User $user) => $user->isAdmin());
