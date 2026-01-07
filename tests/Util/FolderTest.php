<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class FolderTest extends TestCase
{
	private string $testDir;

	protected function setUp(): void
	{
		$this->testDir = sys_get_temp_dir() . '/breeze_test_' . uniqid();
		mkdir($this->testDir);
	}

	protected function tearDown(): void
	{
		if (is_dir($this->testDir)) {
			$this->removeDirectory($this->testDir);
		}
	}

	private function removeDirectory(string $dir): void
	{
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$path = $dir . '/' . $file;
				if (is_dir($path)) {
					$this->removeDirectory($path);
				} else {
					unlink($path);
				}
			}
		}
		rmdir($dir);
	}

	public function testGetFilesInFolderWithValidDirectory(): void
	{
		// Create some test files
		touch($this->testDir . '/file1.txt');
		touch($this->testDir . '/file2.txt');
		touch($this->testDir . '/file3.txt');

		$files = Folder::getFilesInFolder($this->testDir);

		$this->assertIsArray($files);
		$this->assertCount(3, $files);
		$this->assertContains('file1.txt', $files);
		$this->assertContains('file2.txt', $files);
		$this->assertContains('file3.txt', $files);
		$this->assertNotContains('.', $files);
		$this->assertNotContains('..', $files);
	}

	public function testGetFilesInFolderWithEmptyDirectory(): void
	{
		$files = Folder::getFilesInFolder($this->testDir);

		$this->assertIsArray($files);
		$this->assertEmpty($files);
	}

	public function testGetFilesInFolderWithNonExistentDirectory(): void
	{
		$files = Folder::getFilesInFolder('/non/existent/path');

		$this->assertIsArray($files);
		$this->assertEmpty($files);
	}

	public function testGetFilesInFolderWithMixedContent(): void
	{
		// Create files and subdirectories
		touch($this->testDir . '/file1.txt');
		mkdir($this->testDir . '/subdir');
		touch($this->testDir . '/file2.txt');

		$files = Folder::getFilesInFolder($this->testDir);

		$this->assertIsArray($files);
		$this->assertCount(3, $files);
		$this->assertContains('file1.txt', $files);
		$this->assertContains('file2.txt', $files);
		$this->assertContains('subdir', $files);
	}
}
