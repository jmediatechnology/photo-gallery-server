<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CreateCommandConstraintValidator extends ConstraintValidator
{
    public function __construct(private PhotographRepository $photographRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof CreateCommand) {
            throw new UnexpectedTypeException($constraint, CreateCommand::class);
        }

        if (!$constraint instanceof CreateCommandConstraint) {
            throw new UnexpectedValueException($constraint, CreateCommandConstraint::class);
        }

        $title = $value->title();
        $photograph = $this->photographRepository->findOneBy([
            'title' => $title
        ]);

        if ($photograph) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
