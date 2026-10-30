<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Query\Photograph\DownloadQuery;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Infrastructure\Symfony\Validator\Photograph\DownloadForKnownPhotographUuidsConstraint;
use App\Infrastructure\Symfony\Validator\Photograph\DownloadForKnownPhotographUuidsConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


final class DownloadForKnownPhotographUuidsConstraintValidatorTest extends ConstraintValidatorTestCase
{
    private PhotographRepositoryInterface|MockObject $photographRepository;

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenConstraintIsNotInstanceOfDownloadForKnownPhotographUuidsConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $value = $this->createStub(DownloadQuery::class);
        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function throwsUnexpectedValueExceptionWhenValueIsNotInstanceOfDownloadQuery(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $value = 'Not instance of DownloadQuery';
        $constraint = $this->createStub(DownloadForKnownPhotographUuidsConstraint::class);
        $this->validator->validate($value, $constraint);
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $photographRepository = $this->createStub(PhotographRepositoryInterface::class);
        $this->photographRepository = $photographRepository;

        return new DownloadForKnownPhotographUuidsConstraintValidator($this->photographRepository);
    }
}
