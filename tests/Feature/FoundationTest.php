<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires sign in before accessing account settings', function (): void {
    $this->get('/settings/profile')
        ->assertRedirect('/login');
});

it('keeps the settings shortcut a read only navigation route', function (): void {
    $this->actingAs(User::factory()
        ->create());

    $this->get('/settings')
        ->assertRedirect('/settings/profile');
    $this->post('/settings')
        ->assertMethodNotAllowed();
});
