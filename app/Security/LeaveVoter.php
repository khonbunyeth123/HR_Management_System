<?php
declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter for authorizing leave operations.
 */
class LeaveVoter extends Voter
{
    public const LEAVE_APPROVE = 'LEAVE_APPROVE';
    public const LEAVE_REJECT = 'LEAVE_REJECT';
    public const LEAVE_STORE = 'LEAVE_STORE';
    public const LEAVE_DESTROY = 'LEAVE_DESTROY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LEAVE_APPROVE, self::LEAVE_REJECT, self::LEAVE_STORE, self::LEAVE_DESTROY]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Subject is the leave entity (array)
        $leave = $subject;

        return match ($attribute) {
            self::LEAVE_APPROVE, self::LEAVE_REJECT => $this->canApproveOrReject($leave, $user),
            self::LEAVE_STORE => $this->canStore($user),
            self::LEAVE_DESTROY => $this->canDestroy($leave, $user),
            default => false,
        };
    }

    private function canApproveOrReject(array $leave, UserInterface $user): bool
    {
        // Check role (e.g., must be an admin or manager)
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_MANAGER', $user->getRoles())) {
            return false;
        }

        // Check department matching
        // In a real Symfony app, $user would have a department property.
        // For this refactor, we assume $user->getDepartment() exists or similar.
        // And $leave['department'] is available.
        return $user->getDepartment() === ($leave['department'] ?? null);
    }

    private function canStore(UserInterface $user): bool
    {
        return true; // Any authenticated user can apply for leave
    }

    private function canDestroy(array $leave, UserInterface $user): bool
    {
        // Only the owner can cancel their own leave application if it's still pending
        return $leave['employee_id'] === $user->getId() && $leave['status_id'] === 0;
    }
}
