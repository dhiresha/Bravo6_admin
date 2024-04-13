<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Role;
use App\Entity\Folder;

final class CurrentUserFoldersExtension implements QueryCollectionExtensionInterface
{
	public function __construct(private readonly Security $security){}

	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
	{
		if ($resourceClass !== Folder::class) {
            return;
        }

        $user = $this->security->getUser();
        $roles = $user->getRoles();

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(sprintf('%s.rolesAllowed', $rootAlias), 'r')
            ->andWhere(sprintf('%s.owner = :owner', $rootAlias))
            ->orWhere('r.code IN (:roles)')
            ->setParameter('owner', $user)
            ->setParameter('roles', $roles);
	}
}