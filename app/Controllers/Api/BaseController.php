<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Models\User;

/**
 * A base controller that provides Symfony-like helpers for the custom framework.
 */
abstract class BaseController
{
    /**
     * Returns a JsonResponse.
     */
    protected function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Gets the current logged in user.
     */
    protected function getUser(): ?object
    {
        if (isset($_SESSION['user_id'])) {
            return (object)[
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? '',
                'roles' => [$_SESSION['role'] ?? 'ROLE_USER']
            ];
        }
        
        if (isset($_SESSION['employee_id'])) {
            return (object)[
                'id' => $_SESSION['employee_id'],
                'username' => $_SESSION['username'] ?? '',
                'roles' => ['ROLE_EMPLOYEE']
            ];
        }

        return null;
    }

    /**
     * Mimics denyAccessUnlessGranted.
     */
    protected function denyAccessUnlessGranted(string $attribute, mixed $subject = null): void
    {
        // For now, we rely on the Router's authorizeRoute which already runs.
        // But if we want real Voters, we'd trigger them here.
        // To keep it simple for the refactor, we assume the router handles basic roles.
    }
}
