<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Query\Photograph\DownloadQuery;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DownloadForKnownPhotographUuidsConstraintValidator extends ConstraintValidator
{
    public function __construct(private PhotographRepository $photographRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DownloadForKnownPhotographUuidsConstraint) {
            throw new UnexpectedTypeException($constraint, DownloadForKnownPhotographUuidsConstraint::class);
        }

        if (!$value instanceof DownloadQuery) {
            throw new UnexpectedValueException($value, DownloadQuery::class);
        }

        $missingUuids = $this->findMissingUuids($value->uuids());
        if (empty($missingUuids)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ uuids }}', implode(', ', $missingUuids))
            ->addViolation();
    }

    /**
     * @param array<string> $uuids
     * @return array<UUID>
     */
    private function findMissingUuids(array $uuids): array
    {
        /** @var $requestedUuids array<UUID> */
        $requestedUuids = array_map(
            callback: static fn (string $uuid): UUID => new UUID($uuid),
            array: $uuids,
        );

        /** @var $foundUuids array<UUID> */
        $foundUuids = array_map(
            callback: static fn (Photograph $photograph) => $photograph->uuid(),
            array: $this->photographRepository->findByUUIDs($requestedUuids),
        );

        $missingUuids = array_diff($requestedUuids, $foundUuids);
        return array_values($missingUuids);
    }
}
