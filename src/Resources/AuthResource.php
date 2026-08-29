<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\UserData;

class AuthResource extends BaseResource
{
    /**
     * Authenticate and obtain a Personal Access Bearer Token.
     */
    public function login(
        string $email,
        string $password,
        ?string $organizationSlug = null,
        ?string $deviceName = 'Venue Family SDK'
    ): array {
        $payload = [
            'email' => $email,
            'password' => $password,
            'device_name' => $deviceName,
        ];

        if ($organizationSlug !== null) {
            $payload['organization_slug'] = $organizationSlug;
        }

        return $this->client->post('auth/login', $payload);
    }

    /**
     * Register a new user account.
     */
    public function register(array $userData): array
    {
        return $this->client->post('auth/register', $userData);
    }

    /**
     * Retrieve the currently authenticated user.
     */
    public function user(): UserData
    {
        $response = $this->client->get('auth/user');
        $item = $response['user'] ?? $response['data'] ?? $response;

        return UserData::fromArray($item);
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function updateProfile(array $attributes): UserData
    {
        $response = $this->client->put('auth/profile', $attributes);
        $item = $response['user'] ?? $response['data'] ?? $response;

        return UserData::fromArray($item);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(
        string $currentPassword,
        string $newPassword,
        string $newPasswordConfirmation
    ): array {
        return $this->client->put('auth/password', [
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPasswordConfirmation,
        ]);
    }

    /**
     * Request a password reset notification.
     */
    public function forgotPassword(string $email, ?string $organizationSlug = null): array
    {
        $payload = ['email' => $email];
        if ($organizationSlug !== null) {
            $payload['organization_slug'] = $organizationSlug;
        }

        return $this->client->post('auth/forgot-password', $payload);
    }

    /**
     * Reset password using a reset token.
     */
    public function resetPassword(
        string $token,
        string $email,
        string $password,
        string $passwordConfirmation
    ): array {
        return $this->client->post('auth/reset-password', [
            'token' => $token,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);
    }

    /**
     * Refresh the current session token.
     */
    public function refreshToken(?string $deviceName = 'Venue Family SDK'): array
    {
        return $this->client->post('auth/refresh', [
            'device_name' => $deviceName,
        ]);
    }

    /**
     * Log out and invalidate the current access token.
     */
    public function logout(): array
    {
        return $this->client->post('auth/logout');
    }

    /**
     * Revoke all personal access tokens for the authenticated user.
     */
    public function revokeAllTokens(): array
    {
        return $this->client->post('auth/revoke-all');
    }
}
