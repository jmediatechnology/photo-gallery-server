<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Validator\Photograph\CreateCommandConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


final class CreateCommandConstraintValidatorTest extends ConstraintValidatorTestCase
{
    private PhotographRepository|MockObject $photographRepository;

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenValueIsNotInstanceOfCreatePhotograph(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $value = '';
        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenConstraintIsNotInstanceOfCreatePhotographConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $value = $this->createStub(CreateCommand::class);
        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate($value, $constraint);
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $photographRepository = $this->createStub(PhotographRepository::class);
        $this->photographRepository = $photographRepository;

        return new CreateCommandConstraintValidator($this->photographRepository);
    }
}
