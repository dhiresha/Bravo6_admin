<?php

namespace App\Security\Voter;

use App\Entity\Folder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FolderVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Folder;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

		/** @var Folder $folder */
		$folder = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
                if ($folder->getOwner() === $user) {
                    return true;
                }
                foreach ($folder->getRolesAllowed() as $role) {
                    /** @var Role $role */
                    if (in_array($role->getCode(), $user->getRoles())) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }
}
