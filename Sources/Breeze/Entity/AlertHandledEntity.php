<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeImmutable;

class AlertHandledEntity extends AlertEntity
{
	public const string SENDER_ID = 'sender_id';
	public const string SENDER_NAME = 'sender_name';
	public const string SENDER_EMAIL = 'sender_email';
	public const string SENDER_AVATAR = 'sender_avatar';
	public const string SENDER_FILENAME = 'sender_filename';
	public const string TIME = 'time';
	public const string VISIBLE = 'visible';
	public const string SHOW_LINKS = 'show_links';
	public const string ICON = 'icon';
	public const string TARGET_HREF = 'target_href';
	public const string TEXT = 'text';

	protected int $sender_id = 0;

	protected string $sender_name = '';

	protected string $sender_email = '';

	protected string $sender_avatar = '';

	protected string | null $sender_filename = null;

	protected int | DateTimeImmutable $time = 0;

	protected bool $visible = false;

	protected bool $show_links = false;

	protected string $icon = '';

	protected string $target_href = '';

	protected string $text = '';

	/**
	 * @throws DateMalformedStringException
	 */
	public function getTime(): DateTimeImmutable
	{
		return is_int($this->alert_time) ? new DateTimeImmutable('@' . $this->alert_time) : $this->alert_time;
	}

	public function setTime(int | DateTimeImmutable $time): void
	{
		$this->alert_time = $time;
	}

	public static function getColumns(): array
	{
		return [
			self::SENDER_ID,
			self::SENDER_NAME,
			self::SENDER_EMAIL,
			self::SENDER_AVATAR,
			self::SENDER_FILENAME,
			self::TIME,
			self::VISIBLE,
			self::SHOW_LINKS,
			self::ICON,
			self::TARGET_HREF,
			self::TEXT,
		];
	}

	public function getSenderId(): int
	{
		return $this->sender_id;
	}

	public function setSenderId(int $sender_id): void
	{
		$this->sender_id = $sender_id;
	}

	public function getSenderName(): string
	{
		return $this->sender_name;
	}

	public function setSenderName(string $sender_name): void
	{
		$this->sender_name = $sender_name;
	}

	public function getSenderEmail(): string
	{
		return $this->sender_email;
	}

	public function setSenderEmail(string $sender_email): void
	{
		$this->sender_email = $sender_email;
	}

	public function getSenderAvatar(): string
	{
		return $this->sender_avatar;
	}

	public function setSenderAvatar(string $sender_avatar): void
	{
		$this->sender_avatar = $sender_avatar;
	}

	public function getSenderFilename(): ?string
	{
		return $this->sender_filename;
	}

	public function setSenderFilename(?string $sender_filename): void
	{
		$this->sender_filename = $sender_filename;
	}

	public function isVisible(): bool
	{
		return $this->visible;
	}

	public function setVisible(bool $visible): void
	{
		$this->visible = $visible;
	}

	public function isShowLinks(): bool
	{
		return $this->show_links;
	}

	public function setShowLinks(bool $show_links): void
	{
		$this->show_links = $show_links;
	}

	public function getIcon(): string
	{
		return $this->icon;
	}

	public function setIcon(string $icon): void
	{
		$this->icon = $icon;
	}

	public function getTargetHref(): string
	{
		return $this->target_href;
	}

	public function setTargetHref(string $target_href): void
	{
		$this->target_href = $target_href;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setText(string $text): void
	{
		$this->text = $text;
	}
}
