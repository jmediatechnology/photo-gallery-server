<?php

namespace App\Presentation\Resolver\Photograph;

use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;
use App\Presentation\DTO\Photograph\GetAllInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetAllInputDTOResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== GetAllInputDTO::class) {
            return [];
        }

        yield new GetAllInputDTO(
            title: $this->resolveTitle($request),
            sortField: $this->resolveSortField($request),
            sortDirection: $this->resolveSortDirection($request),
        );
    }

    private function resolveTitle(Request $request): ?string
    {
        $title = null;

        $contentType = $request->headers->get('Content-Type');
        if ($contentType === 'application/json') {
            $title = $request->getPayload()->get('title');
        }

        if (in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data'], true)) {
            $title = $request->request->get('title');
        }

        if ($title === null) {
            $title = $request->query->get('title');
        }

        return $title;
    }

    private function resolveSortField(Request $request): ?PhotographSortField
    {
        $sortField = null;

        $contentType = $request->headers->get('Content-Type');
        if ($contentType === 'application/json') {
            $sortField = $request->getPayload()->get('sortField');
        }

        if ($sortField === null) {
            $sortField = $request->query->get('sortField');
        }

        if (!$sortField) {
            return null;
        }

        $photographSortField = PhotographSortField::tryFrom($sortField);
        if ($photographSortField === null) {
            $cases = implode(', ', array_column(PhotographSortField::cases(), 'value'));
            throw new BadRequestHttpException(
                message: sprintf('Invalid sortField "%s". Allowed: %s', $sortField, $cases),
            );
        }

        return $photographSortField;
    }

    private function resolveSortDirection(Request $request): ?PhotographSortDirection
    {
        $sortDirection = null;

        $contentType = $request->headers->get('Content-Type');
        if ($contentType === 'application/json') {
            $sortDirection = $request->getPayload()->get('sortDirection');
        }

        if ($sortDirection === null) {
            $sortDirection = $request->query->get('sortDirection');
        }

        if (!$sortDirection) {
            return null;
        }

        $photographSortDirection = PhotographSortDirection::tryFrom($sortDirection);
        if ($photographSortDirection === null) {
            $cases = implode(', ', array_column(PhotographSortDirection::cases(), 'value'));
            throw new BadRequestHttpException(
                message: sprintf('Invalid sortDirection "%s". Allowed: %s', $sortDirection, $cases),
            );
        }

        return $photographSortDirection;
    }
}
