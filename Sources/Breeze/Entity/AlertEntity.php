<?php

declare(strict_types=1);


namespace Breeze\Entity;

class AlertEntity extends NormalizeEntity
{
	public const TABLE = 'user_alerts';
	public const COLUMN_ID = 'id_alert';
	public const COLUMN_ALERT_TIME = 'alert_time';
	public const COLUMN_ID_MEMBER = 'id_member';
	public const COLUMN_ID_MEMBER_STARTED = 'id_member_started';
	public const COLUMN_MEMBER_NAME = 'member_name';
	public const COLUMN_CONTENT_TYPE = 'content_type';
	public const COLUMN_CONTENT_ID = 'content_id';
	public const COLUMN_CONTENT_ACTION = 'content_action';
	public const COLUMN_IS_READ = 'is_read';
	public const COLUMN_EXTRA = 'extra';

	public const PROPERTY_ID = 'id';
	public const PROPERTY_TIME = 'time';
	public const PROPERTY_ID_MEMBER = 'idMember';
	public const PROPERTY_ID_MEMBER_STARTED = 'idMemberStarted';
	public const PROPERTY_MEMBER_NAME = 'memberName';
	public const PROPERTY_TYPE = 'type';
	public const PROPERTY_CONTENT_ID = 'contentId';
	public const PROPERTY_ACTION = 'action';
	public const PROPERTY_IS_READ = 'isRead';
	public const PROPERTY_EXTRA = 'extra';
	public const KEY_MAP = [
		self::COLUMN_ID => self::PROPERTY_ID,
		self::COLUMN_ALERT_TIME => self::PROPERTY_TIME,
		self::COLUMN_ID_MEMBER => self::PROPERTY_ID_MEMBER,
		self::COLUMN_ID_MEMBER_STARTED => self::PROPERTY_ID_MEMBER_STARTED,
		self::COLUMN_MEMBER_NAME => self::PROPERTY_MEMBER_NAME,
		self::COLUMN_CONTENT_TYPE => self::PROPERTY_TYPE,
		self::COLUMN_CONTENT_ID => self::PROPERTY_CONTENT_ID,
		self::COLUMN_CONTENT_ACTION => self::PROPERTY_ACTION,
		self::COLUMN_IS_READ => self::PROPERTY_IS_READ,
		self::COLUMN_EXTRA => self::PROPERTY_EXTRA,
	];

	protected int $id = 0;

	protected string $time = '';

	protected int $idMember = 0;

	protected int $idMemberStarted = 0;

	protected string $memberName = '';

	protected string $type = '';

	protected int $contentId = 0;

	protected string $action = '';

	protected bool $isRead = false;

	protected string $extra = '';

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getTime(): string
	{
		return $this->time;
	}

	public function setTime(string $time): void
	{
		$this->time = $time;
	}

	public function getIdMember(): int
	{
		return $this->idMember;
	}

	public function setIdMember(int $idMember): void
	{
		$this->idMember = $idMember;
	}

	public function getIdMemberStarted(): int
	{
		return $this->idMemberStarted;
	}

	public function setIdMemberStarted(int $idMemberStarted): void
	{
		$this->idMemberStarted = $idMemberStarted;
	}

	public function getMemberName(): string
	{
		return $this->memberName;
	}

	public function setMemberName(string $memberName): void
	{
		$this->memberName = $memberName;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function getContentId(): int
	{
		return $this->contentId;
	}

	public function setContentId(int $contentId): void
	{
		$this->contentId = $contentId;
	}

	public function getAction(): string
	{
		return $this->action;
	}

	public function setAction(string $action): void
	{
		$this->action = $action;
	}

	public function isRead(): bool
	{
		return $this->isRead;
	}

	public function setIsRead(bool|int $isRead): void
	{
		$this->isRead = (bool) $isRead;
	}

	public function getExtra(): string
	{
		return $this->extra;
	}

	public function setExtra(string $extra): void
	{
		$this->extra = $extra;
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

	public function getColumnMap(): array
	{
		return self::KEY_MAP;
	}
}
