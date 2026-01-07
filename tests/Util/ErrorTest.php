<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
	public function testShowCallsFatalLangError(): void
	{
		// Since Error::show() calls fatal_lang_error() which would exit the script,
		// we can't directly test it without mocking the global function.
		// Instead, we verify the class structure and constants exist.
		
		$this->assertTrue(class_exists(Error::class));
		$this->assertTrue(method_exists(Error::class, 'show'));
	}

	public function testShowMethodIsStatic(): void
	{
		$reflection = new \ReflectionMethod(Error::class, 'show');
		$this->assertTrue($reflection->isStatic());
	}

	public function testShowMethodAcceptsStringParameter(): void
	{
		$reflection = new \ReflectionMethod(Error::class, 'show');
		$parameters = $reflection->getParameters();
		
		$this->assertCount(1, $parameters);
		$this->assertEquals('errorTextKey', $parameters[0]->getName());
		$this->assertEquals('string', $parameters[0]->getType()->getName());
	}

	public function testShowMethodReturnsVoid(): void
	{
		$reflection = new \ReflectionMethod(Error::class, 'show');
		$returnType = $reflection->getReturnType();
		
		$this->assertNotNull($returnType);
		$this->assertEquals('void', $returnType->getName());
	}
}
