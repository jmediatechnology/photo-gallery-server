<?php

namespace App\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\InputDTO;
use JsonException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class InputDTOResolver implements ValueResolverInterface
{
    /**
     * @throws JsonException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== InputDTO::class) {
            return [];
        }

        $fileBag = $request->files;

        /** @var $file UploadedFile|null */
        $file = $fileBag->has(0) ? $fileBag->get(0) : null;

        if (!$file instanceof UploadedFile) {
            throw new HttpException(
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                message: 'You must upload exactly one file.'
            );
        }

        $contentType = $request->headers->get('Content-Type');
        if ($contentType === 'application/json') {
            throw new UnsupportedMediaTypeHttpException(
                'File uploads are not allowed with Content-Type: application/json. Use multipart/form-data instead.'
            );
        }

        $uuid = $request->request->get('uuid');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        yield InputDTO::create(
            uuid: $uuid,
            title: $title,
            description: $description,
            file: $file,
        );
    }
}
