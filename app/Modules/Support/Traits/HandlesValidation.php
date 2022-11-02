<?php

namespace App\Modules\Support\Traits;

use App\Modules\Http\Problem;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Trait HandlessValidation
 */
trait HandlesValidation
{
    /**
     * Handle ValidationException and return proper RFC7807 response
     * 
     * @todo Should the exception be a custom exception implementing
     *          App\Modules\Http\Problem ?
     * 
     * @param ValidationException|MessageBag $error
     * 
     * @return Illuminate\Http\Response
     */
    public function handleValidationError($error): Response
    {
        if ($error instanceof ValidationException) {
            $errorBag = $this->extractErrorsFromBag($error->validator->errors());
        } else if ($error instanceof MessageBag) {
            $errorBag = $this->extractErrorsFromBag($error);
        } else {
            throw new InvalidArgumentException('$error must be an instace of Illuminate\Validation\ValidationException or Illuminate\Support\MessageBag.');
        }

        $problem = new Problem();
        $problem->setTitle("The given data was invalid");
        $problem->setErrorCode("Err:user_input_invalid");
        $problem->setStatus(400);
        $problem->setData([
            "errors" => $errorBag,
        ]);
        return $problem->render();
    }

    /**
     * Get the errors from the MessageBag
     * 
     * @param MessageBag $messageBag
     * 
     * @return array
     */
    protected function extractErrorsFromBag(MessageBag $messageBag): array
    {
        return $messageBag->getMessages();
    }
}