<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\Title;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UpdateCommandConstraintValidator extends ConstraintValidator
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

    /** @var $value UpdateCommand */
    /** @var $constraint UpdateCommandConstraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof UpdateCommand) {
            throw new UnexpectedTypeException($constraint, UpdateCommand::class);
        }

        if (!$constraint instanceof UpdateCommandConstraint) {
            throw new UnexpectedValueException($constraint, UpdateCommandConstraint::class);
        }

        $oldTitle = $value->photograph()->title()->__toString();
        $newTitle = $value->title();
        if ($oldTitle === $newTitle) {
            return;
        }

        $title = new Title($newTitle);
        $photograph = $this->photographRepository->findOneByTitle($title);

        if ($photograph) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
