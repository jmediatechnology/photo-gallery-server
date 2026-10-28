<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\Title;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CreateCommandConstraintValidator extends ConstraintValidator
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof CreateCommand) {
            throw new UnexpectedTypeException($constraint, CreateCommand::class);
        }

        if (!$constraint instanceof CreateCommandConstraint) {
            throw new UnexpectedValueException($constraint, CreateCommandConstraint::class);
        }

        $title = $value->title();

        $photograph = $this->photographRepository->findOneByTitle(new Title($title));

        if ($photograph) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
