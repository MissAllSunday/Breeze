<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\Util\Json;
use DateTimeImmutable;

class AlertEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'user_alerts';
	public const string COLUMN_ID = 'id_alert';
	public const string COLUMN_ALERT_TIME = 'alert_time';
	public const string COLUMN_ID_MEMBER = 'id_member';
	public const string COLUMN_ID_MEMBER_STARTED = 'id_member_started';
	public const string COLUMN_MEMBER_NAME = 'member_name';
	public const string COLUMN_CONTENT_TYPE = 'content_type';
	public const string COLUMN_CONTENT_ID = 'content_id';
	public const string COLUMN_CONTENT_ACTION = 'content_action';
	public const string COLUMN_IS_READ = 'is_read';
	public const string COLUMN_EXTRA = 'extra';

	protected int $id_alert = 0;

	protected int $alert_time = 0;

	protected int $id_member = 0;

	protected int $id_member_started = 0;

	protected string $member_name = '';

	protected string $content_type = '';

	protected int $content_id = 0;

	protected string $content_action = '';

	protected int $is_read = 0;

	protected string $extra = '';

	public function getIdAlert(): int
	{
		return $this->id_alert;
	}

	public function setIdAlert(int $id): void
	{
		$this->id_alert = $id;
	}

	public function unsetIdAlert():void
	{
		unset($this->id_alert);
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function getAlertTime(): DateTimeImmutable
	{
		return new DateTimeImmutable('@' . $this->alert_time);
	}

	public function setAlertTime(int | DateTimeImmutable $time): void
	{
		$this->alert_time = is_int($time) ? $time : $time->getTimestamp();
	}

	public function getIdMember(): int
	{
		return $this->id_member;
	}

	public function setIdMember(int $idMember): void
	{
		$this->id_member = $idMember;
	}

	public function getIdMemberStarted(): int
	{
		return $this->id_member_started;
	}

	public function setIdMemberStarted(int $idMemberStarted): void
	{
		$this->id_member_started = $idMemberStarted;
	}

	public function getMemberName(): string
	{
		return $this->member_name;
	}

	public function setMemberName(string $memberName): void
	{
		$this->member_name = $memberName;
	}

	public function getContentType(): string
	{
		return $this->content_type;
	}

	public function setContentType(string $type): void
	{
		$this->content_type = $type;
	}

	public function getContentId(): int
	{
		return $this->content_id;
	}

	public function setContentId(int $contentId): void
	{
		$this->content_id = $contentId;
	}

	public function getContentAction(): string
	{
		return $this->content_action;
	}

	public function setContentAction(string $action): void
	{
		$this->content_action = $action;
	}

	public function isRead(): bool
	{
		return (bool) $this->is_read;
	}

	public function setIsRead(bool|int $isRead): void
	{
		$this->is_read = (int) $isRead;
	}

	public function getExtra(): array
	{
		return Json::decode($this->extra);
	}

	public function setExtra(string | array $extra): void
	{
		$this->extra = is_array($extra) ? Json::encode($extra) : $extra;
	}

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_ALERT_TIME,
			self::COLUMN_ID_MEMBER,
			self::COLUMN_ID_MEMBER_STARTED,
			self::COLUMN_MEMBER_NAME,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_CONTENT_ACTION,
			self::COLUMN_IS_READ,
			self::COLUMN_EXTRA,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int|DateTimeImmutable
	{
		return match ($columnName) {
			self::COLUMN_ID,
			self::COLUMN_ID_MEMBER,
			self::COLUMN_ID_MEMBER_STARTED,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_IS_READ => (int) $value,
			self::COLUMN_ALERT_TIME => new DateTimeImmutable('@' . $value),
			self::COLUMN_EXTRA => Json::decode($value),
			default => (string) $value,
		};
	}
}
