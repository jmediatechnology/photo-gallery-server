<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\UUID;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class GenerateDescriptionForKnownPhotographUuidConstraintValidator extends ConstraintValidator
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

    /** @var $value GenerateDescriptionForKnownPhotographUuidQuery */
    /** @var $constraint GenerateDescriptionForKnownPhotographUuidConstraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof GenerateDescriptionForKnownPhotographUuidQuery) {
            throw new UnexpectedTypeException($constraint, GenerateDescriptionForKnownPhotographUuidQuery::class);
        }

        if (!$constraint instanceof GenerateDescriptionForKnownPhotographUuidConstraint) {
            throw new UnexpectedValueException($constraint, GenerateDescriptionForKnownPhotographUuidConstraint::class);
        }

        $uuid = $value->uuid();
        $photograph = $this->photographRepository->findOneByUUID(new UUID($uuid));

        if ($photograph === null) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
