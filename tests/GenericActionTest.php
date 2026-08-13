<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Local copy of Payum\Core\Tests\GenericActionTest: payum/core marks its own
 * Tests directory as export-ignore, so the class is absent from the dist
 * archive Composer installs.
 */
abstract class GenericActionTest extends TestCase
{
    /**
     * Not bound to Generic: Convert is also driven by this test case and does
     * not extend it.
     *
     * @var class-string
     */
    protected $requestClass;

    /**
     * @var class-string<ActionInterface>
     */
    protected $actionClass;

    /**
     * @var ActionInterface
     */
    protected $action;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass();
    }

    public function provideSupportedRequests(): \Generator
    {
        yield [new $this->requestClass([])];
        yield [new $this->requestClass(new \ArrayObject())];
    }

    public function provideNotSupportedRequests(): \Generator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [new $this->requestClass('foo')];
        yield [new $this->requestClass(new \stdClass())];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface(): void
    {
        $rc = new \ReflectionClass($this->actionClass);

        static::assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     * @dataProvider provideSupportedRequests
     *
     * @param mixed $request
     */
    public function shouldSupportRequest($request): void
    {
        static::assertTrue($this->action->supports($request));
    }

    /**
     * @test
     * @dataProvider provideNotSupportedRequests
     *
     * @param mixed $request
     */
    public function shouldNotSupportRequest($request): void
    {
        static::assertFalse($this->action->supports($request));
    }

    /**
     * @test
     * @dataProvider provideNotSupportedRequests
     *
     * @param mixed $request
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute($request);
    }

    /**
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }

    /**
     * @return MockObject&TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->createMock(TokenInterface::class);
    }
}
