<?php

namespace YouzanApiDataBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use YouzanApiDataBundle\Exception\DateGenerationException;

/**
 * @internal
 */
#[CoversClass(DateGenerationException::class)]
final class DateGenerationExceptionTest extends AbstractExceptionTestCase
{
    public function testDateGenerationExceptionIsRuntimeException(): void
    {
        $exception = new DateGenerationException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testDateGenerationExceptionWithMessageAndCode(): void
    {
        $message = 'Failed to generate date';
        $code = 123;

        $exception = new DateGenerationException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testDateGenerationExceptionWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new DateGenerationException('Main exception', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
