<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\EntityInterface;
use Breeze\Repository\User\SettingsRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[AllowMockObjectsWithoutExpectations]
class BaseRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private AlertRepository $repository;

	private stdClass $queryObject;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->repository = new AlertRepository($this->dbClient);
		$this->queryObject = new stdClass();
	}

	public function testDoesContentExistsEmitsWhereClause(): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->with(
				$this->logicalAnd(
					$this->stringContains('WHERE {raw:columnName} = {int:id}'),
					$this->stringContains('FROM {db_prefix}{raw:from}'),
					$this->stringContains('LIMIT 1')
				),
				$this->equalTo([
					'from' => 'user_alerts AS parent',
					'columns' => 'parent.id_alert, parent.alert_time, parent.id_member, ' .
						'parent.id_member_started, parent.member_name, parent.content_type, ' .
						'parent.content_id, parent.content_action, parent.is_read, parent.extra',
					'tableName' => 'user_alerts',
					'columnName' => 'parent.' . AlertEntity::ID,
					'id' => 5,
				])
			)
			->willReturn($this->queryObject);

		$this->dbClient->expects($this->once())
			->method('numRows')
			->with($this->queryObject)
			->willReturn(1);

		$this->dbClient->expects($this->once())
			->method('freeResult')
			->with($this->queryObject);

		$this->assertTrue($this->repository->doesContentExists(5));
	}

	public function testDoesContentExistsReturnsFalseWhenNoRowMatches(): void
	{
		// The table holds other rows; only the requested id is missing. A
		// query without a predicate would report true here.
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($this->queryObject);

		$this->dbClient->expects($this->once())
			->method('numRows')
			->with($this->queryObject)
			->willReturn(0);

		$this->dbClient->expects($this->once())
			->method('freeResult')
			->with($this->queryObject);

		$this->assertFalse($this->repository->doesContentExists(999999));
	}

	public function testDoesContentExistsBindsTheRequestedId(): void
	{
		$boundIds = [];

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturnCallback(function (string $query, array $bindParams) use (&$boundIds) {
				$boundIds[] = $bindParams['id'];

				return $this->queryObject;
			});

		$this->dbClient->method('numRows')->willReturn(1);

		$this->repository->doesContentExists(42);

		$this->assertSame([42], $boundIds);
	}

	public function testDoesContentExistsThrowsWhenIdColumnIsUnknown(): void
	{
		$repository = $this->brokenRepository();

		$this->dbClient->expects($this->never())
			->method('query');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($this->internalErrorText());

		$repository->doesContentExists(1);
	}

	/**
	 * The exception message is what a controller can hand back to the client,
	 * so it must be the language string and nothing else: no class name, no
	 * column name, no dump of the known columns.
	 */
	public function testDoesContentExistsDoesNotLeakSchemaDetails(): void
	{
		$repository = $this->brokenRepository();

		try {
			$repository->doesContentExists(1);
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException $exception) {
			$this->assertSame($this->internalErrorText(), $exception->getMessage());
			$this->assertStringNotContainsString('not_a_column', $exception->getMessage());
			$this->assertStringNotContainsString('breeze_broken', $exception->getMessage());
			$this->assertStringNotContainsString('doesContentExists', $exception->getMessage());
		}
	}

	public function testGetCountThrowsWhenColumnNameIsUnknown(): void
	{
		$this->dbClient->expects($this->never())
			->method('query');

		try {
			$this->repository->getCount(['columnName' => 'not_a_column', 'ids' => [1]]);
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException $exception) {
			$this->assertSame($this->internalErrorText(), $exception->getMessage());
			$this->assertStringNotContainsString('not_a_column', $exception->getMessage());
			$this->assertStringNotContainsString('getCount', $exception->getMessage());
		}
	}

	public function testGetCountThrowsWhenIdsAreEmpty(): void
	{
		$this->dbClient->expects($this->never())
			->method('query');

		try {
			$this->repository->getCount(['columnName' => AlertEntity::ID, 'ids' => []]);
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException $exception) {
			$this->assertSame($this->internalErrorText(), $exception->getMessage());
			$this->assertStringNotContainsString('ids', $exception->getMessage());
		}
	}

	/**
	 * `doesContentExists()` gates on `isValidColumn($this->getColumnId())`, so
	 * every concrete repository must declare its id column among its known
	 * columns. Both values are hardcoded per repository, which makes a
	 * mismatch a coding error rather than a runtime condition: assert it here,
	 * at CI time, instead of relying on the error log to surface it.
	 *
	 * @throws Exception
	 */
	public function testEveryRepositoryDeclaresItsIdColumnAmongKnownColumns(): void
	{
		$dbClient = $this->createMock(ClientInterface::class);
		$commentRepository = new CommentRepository($dbClient);
		$likeRepository = new LikeRepository($dbClient);

		$repositories = [
			new AlertRepository($dbClient),
			$commentRepository,
			$likeRepository,
			new StatusRepository($dbClient, $commentRepository, $likeRepository),
			new SettingsRepository($dbClient),
		];

		foreach ($repositories as $repository) {
			$this->assertContains(
				$repository->getColumnId(),
				$repository->getColumns(),
				sprintf(
					'%s::getColumnId() returns "%s", which is not part of its own getColumns().',
					$repository::class,
					$repository->getColumnId()
				)
			);

			$this->assertTrue(
				$repository->isValidColumn($repository->getColumnId()),
				$repository::class . '::isValidColumn() rejects its own id column.'
			);
		}
	}

	/**
	 * The log entry is read by forum admins, who cannot act on a schema dump.
	 * It keeps the class name and the offending column — enough to locate the
	 * bug in source — and nothing else.
	 */
	public function testDoesContentExistsLogOmitsKnownColumns(): void
	{
		$repository = $this->loggingBrokenRepository();

		try {
			$repository->doesContentExists(1);
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException) {
		}

		$this->assertCount(1, $repository->messages);
		$this->assertStringContainsString('doesContentExists', $repository->messages[0]);
		$this->assertStringContainsString('not_a_column', $repository->messages[0]);
		$this->assertStringNotContainsString('known columns', $repository->messages[0]);
		$this->assertStringNotContainsString('body', $repository->messages[0]);
	}

	public function testGetCountLogOmitsKnownColumns(): void
	{
		$repository = $this->loggingBrokenRepository();

		try {
			$repository->getCount(['columnName' => 'not_a_column', 'ids' => [1]]);
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException) {
		}

		$this->assertCount(1, $repository->messages);
		$this->assertStringContainsString('getCount', $repository->messages[0]);
		$this->assertStringContainsString('not_a_column', $repository->messages[0]);
		$this->assertStringNotContainsString('known columns', $repository->messages[0]);
		$this->assertStringNotContainsString('body', $repository->messages[0]);
	}

	private function internalErrorText(): string
	{
		return $GLOBALS['txt']['Breeze_error_internal'];
	}

	/**
	 * Only the log sink is overridden; `doesContentExists()` and `getCount()`
	 * are the real BaseRepository implementations.
	 */
	private function loggingBrokenRepository(): LoggingBrokenRepository
	{
		return new LoggingBrokenRepository($this->dbClient);
	}

	private function brokenRepository(): BaseRepository
	{
		return new class($this->dbClient) extends BaseRepository {
			public function getTableName(): string
			{
				return 'breeze_broken';
			}

			public function getColumnId(): string
			{
				return 'not_a_column';
			}

			public function getColumns(): array
			{
				return ['id', 'body'];
			}

			public function getColumnPosterId(): string
			{
				return 'user_id';
			}

			public function getById(int $id): ?EntityInterface
			{
				return null;
			}
		};
	}
}

/**
 * Same broken shape as BaseRepositoryTest::brokenRepository(), but with the
 * log sink overridden so the message can be asserted on. Only `logMessage()`
 * is replaced: `doesContentExists()` and `getCount()` are the real
 * BaseRepository implementations.
 */
class LoggingBrokenRepository extends BaseRepository
{
	/**
	 * @var array<int, string>
	 */
	public array $messages = [];

	public function logMessage(string $message): void
	{
		$this->messages[] = $message;
	}

	public function getTableName(): string
	{
		return 'breeze_broken';
	}

	public function getColumnId(): string
	{
		return 'not_a_column';
	}

	public function getColumns(): array
	{
		return ['id', 'body'];
	}

	public function getColumnPosterId(): string
	{
		return 'user_id';
	}

	public function getById(int $id): ?EntityInterface
	{
		return null;
	}
}
