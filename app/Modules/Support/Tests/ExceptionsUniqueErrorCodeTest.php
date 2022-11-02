<?php

namespace App\Modules\Support\Tests;

use App\Modules\Item\Exceptions\UnknownTagException;
use App\Modules\Http\Exceptions\NoErrorCodeException;
use App\Modules\Item\Exceptions\BaseItemModuleException;
use App\Modules\Item\Exceptions\ItemNotFoundException;
use App\Modules\Media\Exceptions\BaseMediaModuleException;
use App\Modules\Media\Exceptions\InvalidValidateOptionException;
use App\Modules\Media\Exceptions\MediaNotAllowedException;
use App\Modules\Storage\Exceptions\BaseStorageModuleException;
use App\Modules\Storage\Exceptions\CannotWriteSecureFileException;
use App\Modules\User\Exceptions\BaseUserModuleException;
use App\Modules\User\Exceptions\EmailAlreadyUsedException;
use App\Modules\User\Exceptions\IncorrectPasswordException;
use App\Modules\User\Exceptions\UnknownStatusException;
use Tests\TestCase;

class ExceptionsUniqueErrorCodeTest extends TestCase
{
    /**
     * List of exceptions that comply to RFC7807
     * 
     * @var array $exceptions
     */
    protected array $exceptions = [
        NoErrorCodeException::class,

        BaseItemModuleException::class,
        ItemNotFoundException::class,
        UnknownTagException::class,

        BaseMediaModuleException::class,
        InvalidValidateOptionException::class,
        MediaNotAllowedException::class,

        BaseStorageModuleException::class,
        CannotWriteSecureFileException::class,

        BaseUserModuleException::class,
        EmailAlreadyUsedException::class,
        IncorrectPasswordException::class,
        UnknownStatusException::class,
    ];

    public function test_exception_error_code_must_be_unique()
    {
        $this->markTestIncomplete('List of exceptions not yet finalized');
        $errorCodes = [];
        foreach ($this->exceptions as $exception) {
            $instance = new $exception;
            array_push($errorCodes, $instance->getErrorCode());
        }

        $initialErrorCodeCount = count($errorCodes);

        // Check if all exceptions added error code
        $this->assertEquals(count($this->exceptions), $initialErrorCodeCount, 'ErrorCode count did not match number of exceptions. Check if all exceptions is returning an errorCode');

        $uniqueErrorCodes = array_unique($errorCodes);
        $uniqueErrorCodesCount = count($uniqueErrorCodes);

        // Check if an error code was removed due to being duplicate
        $this->assertEquals($initialErrorCodeCount, $uniqueErrorCodesCount, 'An exception has duplicate errorCode.');
    }
}