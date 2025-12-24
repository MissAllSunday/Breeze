<?php

declare(strict_types=1);

namespace Breeze;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LikesEnumTest extends TestCase
{
	public function testStatusCase(): void
	{
		$this->assertEquals('br_sta', LikesEnum::Status->value);
	}

	public function testCommentsCase(): void
	{
		$this->assertEquals('br_com', LikesEnum::Comments->value);
	}

	public function testEnumHasTwoCases(): void
	{
		$cases = LikesEnum::cases();
		$this->assertCount(2, $cases);
	}

	public function testEnumCasesAreCorrect(): void
	{
		$cases = LikesEnum::cases();
		$this->assertContains(LikesEnum::Status, $cases);
		$this->assertContains(LikesEnum::Comments, $cases);
	}

	#[DataProvider('validTypesProvider')]
	public function testIsValidWithValidTypes(string $type): void
	{
		$this->assertTrue(LikesEnum::isValid($type));
	}

	public static function validTypesProvider(): array
	{
		return [
			'status type' => ['br_sta'],
			'comments type' => ['br_com'],
		];
	}

	#[DataProvider('invalidTypesProvider')]
	public function testIsValidWithInvalidTypes(string $type): void
	{
		$this->assertFalse(LikesEnum::isValid($type));
	}

	public static function invalidTypesProvider(): array
	{
		return [
			'empty string' => [''],
			'random string' => ['random'],
			'invalid type' => ['br_invalid'],
			'uppercase status' => ['BR_STA'],
			'uppercase comments' => ['BR_COM'],
			'partial match' => ['br_'],
			'status without prefix' => ['sta'],
			'comments without prefix' => ['com'],
			'numeric string' => ['123'],
			'special characters' => ['br_@#$'],
		];
	}

	public function testIsValidIsStaticMethod(): void
	{
		$reflection = new \ReflectionClass(LikesEnum::class);
		$method = $reflection->getMethod('isValid');
		$this->assertTrue($method->isStatic());
	}

	public function testIsValidReturnsBoolean(): void
	{
		$result = LikesEnum::isValid('br_sta');
		$this->assertIsBool($result);
	}

	public function testEnumIsBackedByString(): void
	{
		$reflection = new \ReflectionEnum(LikesEnum::class);
		$this->assertTrue($reflection->isBacked());
		$this->assertEquals('string', $reflection->getBackingType()->getName());
	}

	public function testStatusCaseCanBeUsedInMatch(): void
	{
		$result = match (LikesEnum::Status) {
			LikesEnum::Status => 'status',
			LikesEnum::Comments => 'comments',
		};
		$this->assertEquals('status', $result);
	}

	public function testCommentsCaseCanBeUsedInMatch(): void
	{
		$result = match (LikesEnum::Comments) {
			LikesEnum::Status => 'status',
			LikesEnum::Comments => 'comments',
		};
		$this->assertEquals('comments', $result);
	}

	public function testFromMethodWithValidValue(): void
	{
		$status = LikesEnum::from('br_sta');
		$this->assertSame(LikesEnum::Status, $status);

		$comments = LikesEnum::from('br_com');
		$this->assertSame(LikesEnum::Comments, $comments);
	}

	public function testFromMethodWithInvalidValueThrowsException(): void
	{
		$this->expectException(\ValueError::class);
		LikesEnum::from('invalid');
	}

	public function testTryFromMethodWithValidValue(): void
	{
		$status = LikesEnum::tryFrom('br_sta');
		$this->assertSame(LikesEnum::Status, $status);

		$comments = LikesEnum::tryFrom('br_com');
		$this->assertSame(LikesEnum::Comments, $comments);
	}

	public function testTryFromMethodWithInvalidValueReturnsNull(): void
	{
		$result = LikesEnum::tryFrom('invalid');
		$this->assertNull($result);
	}

	public function testEnumCasesCanBeCompared(): void
	{
		$this->assertSame(LikesEnum::Status, LikesEnum::Status);
		$this->assertNotSame(LikesEnum::Status, LikesEnum::Comments);
	}

	public function testEnumValuesAreUnique(): void
	{
		$values = array_map(fn ($case) => $case->value, LikesEnum::cases());
		$uniqueValues = array_unique($values);
		$this->assertCount(count($values), $uniqueValues);
	}
}
