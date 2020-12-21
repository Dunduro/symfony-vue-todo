<?php


namespace App\Controller\Api;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;

trait FailedValidationTrait
{
    /**
     * @param ValidationFailedException $exception
     * @return JsonResponse
     */
    protected function handleFailedValidation(ValidationFailedException $exception)
    {
        $errors = $exception->getViolations();
        $errorMessages = [];
        for ($i = 0; $i < $errors->count(); $i++) {
            $error = $errors->get($i);
            $errorMessages[] = $error->getMessage();
        }
        return new JsonResponse(['messages' => $errorMessages], Response::HTTP_BAD_REQUEST);;
    }
}