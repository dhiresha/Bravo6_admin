<?php

namespace App\Security\Voter;

use App\Entity\Media;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MediaVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
	public const ALLOW_EDIT = 'ALLOW_EDIT';
	public const ALLOW_DELETE = 'ALLOW_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, 
			[
				self::EDIT,
				self::VIEW,
				self::ALLOW_EDIT,
				self::ALLOW_DELETE
			])
            && $subject instanceof Media;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

		/** @var Media $media */
		$media = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
			case self::ALLOW_EDIT:
				return $media->getOwner() === $user;
			case self::ALLOW_DELETE:
				return $media->getOwner() === $user;
        }

        return false;
    }
}
