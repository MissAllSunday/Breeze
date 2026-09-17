<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Breeze;
use Breeze\Exceptions\ValidateException;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Concrete stand-in used to exercise the abstract dispatcher.
 */
class ApiBaseControllerStub extends ApiBaseController
{
	public bool $subActionWasCalled = false;

	public function __construct(
		ValidateActionsInterface $validateActions,
		ResponseInterface $response,
		SecurityServiceInterface $security,
		private readonly array $subActionsList = [],
		private readonly array $mutatingActionsList = []
	) {
		parent::__construct($validateActions, $response, $security);
	}

	public function known(): void
	{
		$this->subActionWasCalled = true;
	}

	public function getSubActions(): array
	{
		return $this->subActionsList;
	}

	public function getMutatingActions(): array
	{
		return $this->mutatingActionsList;
	}
}

#[AllowMockObjectsWithoutExpectations]
class ApiBaseControllerTest extends TestCase
{
	private const string SUB_ACTION = 'known';

	private ValidateActionsInterface | MockObject $validateActions;

	private ResponseInterface | MockObject $response;

	private SecurityServiceInterface | MockObject $security;

	private ?string $originalRequestMethod = null;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->validateActions = $this->createMock(ValidateActionsInterface::class);
		$this->response = $this->createMock(ResponseInterface::class);
		$this->security = $this->createMock(SecurityServiceInterface::class);

		$this->originalRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	protected function tearDown(): void
	{
		unset($_REQUEST['sa'], $_REQUEST['action'], $_GET['sa'], $_GET['action']);

		if ($this->originalRequestMethod === null) {
			unset($_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->originalRequestMethod;
		}
	}

	private function buildController(
		string $action,
		string $subAction,
		array $subActions = [self::SUB_ACTION],
		array $mutatingActions = []
	): ApiBaseControllerStub {
		$_REQUEST['action'] = $action;
		$_REQUEST['sa'] = $subAction;
		$_GET['action'] = $action;
		$_GET['sa'] = $subAction;

		return new ApiBaseControllerStub(
			$this->validateActions,
			$this->response,
			$this->security,
			$subActions,
			$mutatingActions
		);
	}

	public function testDispatchBailsOutOnUnknownAction(): void
	{
		$controller = $this->buildController('notABreezeAction', self::SUB_ACTION);

		$this->response->expects($this->never())->method('print');
		$this->response->expects($this->never())->method('error');
		$this->validateActions->expects($this->never())->method('setUp');
		$this->validateActions->expects($this->never())->method('isValid');

		$controller->dispatch();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testDispatchReturnsAfter404ForUnknownSubAction(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, 'unknown');

		$this->response->expects($this->once())
			->method('print')
			->with([], ResponseInterface::NOT_FOUND);

		// Nothing past the 404 branch may run.
		$this->security->expects($this->never())->method('validateToken');
		$this->validateActions->expects($this->never())->method('isValid');

		$controller->dispatch();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testDispatchSkipsCsrfAndValidationAfter404EvenForMutatingSubAction(): void
	{
		$controller = $this->buildController(
			Breeze::ACTION_LIKE,
			'unknown',
			[self::SUB_ACTION],
			['unknown']
		);

		$this->response->expects($this->once())
			->method('print')
			->with([], ResponseInterface::NOT_FOUND);

		$this->security->expects($this->never())->method('validateToken');
		$this->validateActions->expects($this->never())->method('isValid');

		$controller->dispatch();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testDispatchCallsDeclaredSubAction(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, self::SUB_ACTION);

		$this->validateActions->expects($this->once())
			->method('setUp')
			->with($this->isArray(), self::SUB_ACTION);

		$this->validateActions->expects($this->once())->method('isValid');
		$this->response->expects($this->never())->method('print');

		$controller->dispatch();

		$this->assertTrue($controller->subActionWasCalled);
	}

	public function testDispatchValidatesCsrfTokenForMutatingSubAction(): void
	{
		$controller = $this->buildController(
			Breeze::ACTION_LIKE,
			self::SUB_ACTION,
			[self::SUB_ACTION],
			[self::SUB_ACTION]
		);

		$this->security->expects($this->once())
			->method('validateToken')
			->with(ResponseInterface::CSRF_TOKEN_ACTION, 'get');

		$controller->dispatch();

		$this->assertTrue($controller->subActionWasCalled);
	}

	public function testDispatchReportsValidateException(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, self::SUB_ACTION);

		$this->validateActions->expects($this->once())
			->method('isValid')
			->willThrowException(new ValidateException('nope'));

		$this->response->expects($this->once())
			->method('error')
			->with('nope', ValidateException::STATUS_CODE);

		$controller->dispatch();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testDispatchWithEmptySubActionFallsThroughToSubActionCall404(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, '');

		$this->validateActions->expects($this->once())->method('isValid');
		$this->response->expects($this->once())
			->method('print')
			->with([], ResponseInterface::NOT_FOUND);

		$controller->dispatch();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testSubActionCheck(): void
	{
		$this->assertTrue(
			$this->buildController(Breeze::ACTION_LIKE, 'unknown')->subActionCheck()
		);

		$this->assertFalse(
			$this->buildController(Breeze::ACTION_LIKE, self::SUB_ACTION)->subActionCheck()
		);

		$this->assertFalse(
			$this->buildController(Breeze::ACTION_LIKE, '')->subActionCheck()
		);
	}

	public function testSubActionCallPrints404ForUndeclaredSubAction(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, 'unknown');

		$this->response->expects($this->once())
			->method('print')
			->with([], ResponseInterface::NOT_FOUND);

		$controller->subActionCall();

		$this->assertFalse($controller->subActionWasCalled);
	}

	public function testSubActionCallInvokesDeclaredSubAction(): void
	{
		$controller = $this->buildController(Breeze::ACTION_LIKE, self::SUB_ACTION);

		$this->response->expects($this->never())->method('print');

		$controller->subActionCall();

		$this->assertTrue($controller->subActionWasCalled);
	}
}
