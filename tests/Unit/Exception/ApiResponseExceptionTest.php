<?php

namespace YouzanApiDataBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use YouzanApiDataBundle\Exception\ApiResponseException;

class ApiResponseExceptionTest extends TestCase
{
    public function testApiResponseExceptionIsRuntimeException(): void
    {
        $exception = new ApiResponseException('Test error message');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testApiResponseExceptionWithMessageAndCode(): void
    {
        $message = 'API response error';
        $code = 500;
        
        $exception = new ApiResponseException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testApiResponseExceptionWithPreviousException(): void
    {
        $previousException = new \Exception('Previous error');
        $exception = new ApiResponseException('Current error', 0, $previousException);
        
        $this->assertSame($previousException, $exception->getPrevious());
    }
}