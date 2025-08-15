<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\Util\Json;
use Breeze\Util\Time;
use DateMalformedStringException;
use DateTimeImmutable;

class AlertEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'user_alerts';
	public const string ID = 'id_alert';
	public const string ALERT_TIME = 'alert_time';
	public const string ID_MEMBER = 'id_member';
	public const string ID_MEMBER_STARTED = 'id_member_started';
	public const string MEMBER_NAME = 'member_name';
	public const string CONTENT_TYPE = 'content_type';
	public const string CONTENT_ID = 'content_id';
	public const string CONTENT_ACTION = 'content_action';
	public const string IS_READ = 'is_read';
	public const string EXTRA = 'extra';
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

	protected int $id_alert = 0;

	protected DateTimeImmutable $alert_time;

	protected int $id_member = 0;

	protected int $id_member_started = 0;

	protected string $member_name = '';

	protected string $content_type = '';

	protected int $content_id = 0;

	protected string $content_action = '';

	protected bool $is_read = false;

	protected array $extra = [];

	protected int $sender_id = 0;

	protected string $sender_name = '';

	protected string $sender_email = '';

	protected string $sender_avatar = '';

	protected string | null $sender_filename = null;

	// SMF's already formated time as string
	protected string $time = '';

	protected bool $visible = false;

	protected bool $show_links = false;

	protected string $icon = '';

	protected string $target_href = '';

	protected string $text = '';

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

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

	public function getAlertTime(): DateTimeImmutable
	{
		return $this->alert_time;
	}

	public function setAlertTime(DateTimeImmutable $time): void
	{
		$this->alert_time = $time;
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
		return $this->is_read;
	}

	public function setIsRead(bool $isRead): void
	{
		$this->is_read = $isRead;
	}

	public function getExtra(): array
	{
		return $this->extra;
	}

	public function setExtra(array $extra): void
	{
		$this->extra = $extra;
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

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::ALERT_TIME,
			self::ID_MEMBER,
			self::ID_MEMBER_STARTED,
			self::MEMBER_NAME,
			self::CONTENT_TYPE,
			self::CONTENT_ID,
			self::CONTENT_ACTION,
			self::IS_READ,
			self::EXTRA,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	public function toInsert(): array
	{
		$this->unsetIdAlert();
		$toInsert = $this->toArray();
		$toInsert[self::IS_READ] = (int) $this->is_read;
		$toInsert[self::EXTRA] = Json::encode($this->extra);
		$toInsert[self::ALERT_TIME] = time();

		return array_intersect_key($toInsert, array_flip(AlertEntity::getColumns()));
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int|DateTimeImmutable|bool|array
	{
		return match ($columnName) {
			self::ID,
			self::ID_MEMBER,
			self::ID_MEMBER_STARTED,
			self::SENDER_ID,
			self::CONTENT_ID => (int) $value,
			self::IS_READ, self::VISIBLE, self::SHOW_LINKS => filter_var($value, \FILTER_VALIDATE_BOOLEAN),
			self::ALERT_TIME => new DateTimeImmutable('@' . $value),
			self::TIME => $value,
			self::EXTRA => is_array($value) ? $value : Json::decode($value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'idAlert' => $this->getIdAlert(),
			'alertTime' => $this->getAlertTime()->getTimestamp(),
			'idMember' => $this->getIdMember(),
			'idMemberStarted' => $this->getIdMemberStarted(),
			'memberName' => $this->getMemberName(),
			'contentType' => $this->getContentType(),
			'contentId' => $this->getContentId(),
			'contentAction' => $this->getContentAction(),
			'isRead' => $this->isRead(),
			'extra' => $this->getExtra(),
			'senderId' => $this->getSenderId(),
			'senderName' => $this->getSenderName(),
			'senderEmail' => $this->getSenderEmail(),
			'senderAvatar' => $this->getSenderAvatar(),
			'senderFilename' => $this->getSenderFilename(),
			'time' => Time::from($this->getAlertTime()),
			'visible' => $this->isVisible(),
			'showLinks' => $this->isShowLinks(),
			'icon' => $this->getIcon(),
			'targetHref' => $this->getTargetHref(),
			'text' => $this->getText(),
		];
	}
}
