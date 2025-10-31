<?php

namespace YouzanApiDataBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use YouzanApiDataBundle\Exception\ApiResponseException;

/**
 * @internal
 */
#[CoversClass(ApiResponseException::class)]
final class ApiResponseExceptionTest extends AbstractExceptionTestCase
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
