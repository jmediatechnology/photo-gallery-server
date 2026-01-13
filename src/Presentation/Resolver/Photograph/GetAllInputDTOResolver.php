<?php

namespace App\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\GetAllInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class GetAllInputDTOResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== GetAllInputDTO::class) {
            return [];
        }

        $title = null;

        $contentType = $request->headers->get('Content-Type');
        if ($contentType === 'application/json') {
            $inputBag = $request->getPayload();
            $title = $inputBag->get('title');
        }

        if (in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data'], true)) {
            $title = $request->request->get('title');
        }

        yield new GetAllInputDTO(
            title: $title,
        );
    }
}
