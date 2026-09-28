<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\Title;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Validator\Photograph\UpdateCommandConstraint;
use App\Infrastructure\Symfony\Validator\Photograph\UpdateCommandConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


final class UpdateCommandConstraintValidatorTest extends ConstraintValidatorTestCase
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

        $value = $this->createStub(UpdateCommand::class);
        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function noViolationWhenOldTitleIsSameAsNewTitle(): void
    {
        $awesomeTitle = 'Awesome Title';

        $title = $this->createMock(Title::class);
        $title
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($awesomeTitle);

        $photograph = $this->createMock(Photograph::class);
        $photograph
            ->expects($this->once())
            ->method('title')
            ->willReturn($title);

        $value = $this->createMock(UpdateCommand::class);
        $value
            ->expects($this->once())
            ->method('photograph')
            ->willReturn($photograph);

        $value
            ->expects($this->once())
            ->method('title')
            ->willReturn($awesomeTitle);

        $constraint = $this->createStub(UpdateCommandConstraint::class);
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $photographRepository = $this->createStub(PhotographRepository::class);
        $this->photographRepository = $photographRepository;

        return new UpdateCommandConstraintValidator($this->photographRepository);
    }
}
