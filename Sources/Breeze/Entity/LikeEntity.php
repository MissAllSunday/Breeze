<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;
use Breeze\Util\Json;
use Breeze\Util\Time;
use DateMalformedStringException;
use DateTimeImmutable;

class LikeEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'user_likes';
	public const string ID_MEMBER = 'id_member';
	public const string TYPE = 'content_type';
	public const string ID = 'content_id';
	public const string TIME = 'like_time';
	public const string IDENTIFIER = 'likes_';
	public const string CAN_LIKE = 'can_like';
	public const string COUNT = 'count';
	public const string ALREADY_LIKED = 'already_liked';
	public const string ADDITIONAL_INFO = 'additional_info';

	public int $id_member = 0;

	protected LikesEnum $content_type;

	protected int $content_id = 0;

	protected DateTimeImmutable $like_time;

	protected int $count = 0;

	protected bool $already_liked = false;

	protected bool $can_like = false;

	protected array $additional_info = [];

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public function getIdMember(): int
	{
		return $this->id_member;
	}

	public function setIdMember(int $idMember): void
	{
		$this->id_member = $idMember;
	}

	public function getContentType(): LikesEnum
	{
		return $this->content_type;
	}

	public function setContentType(LikesEnum $type): void
	{
		$this->content_type = $type;
	}

	public function getContentId(): int
	{
		return $this->content_id;
	}

	public function setContentId(string|int $id): void
	{
		$this->content_id = (int) $id;
	}

	public function getLikeTime(): DateTimeImmutable
	{
		return $this->like_time;
	}

	public function setLikeTime(DateTimeImmutable $time): void
	{
		$this->like_time = $time;
	}

	public function getCount(): int
	{
		return $this->count;
	}

	public function setCount(int $count): void
	{
		$this->count = $count;
	}

	public function isAlreadyLiked(): bool
	{
		return $this->already_liked;
	}

	public function setAlreadyLiked(bool $already_liked): void
	{
		$this->already_liked = $already_liked;
	}

	public function canLike(): bool
	{
		return $this->can_like;
	}

	public function setCanLike(bool $can_like): void
	{
		$this->can_like = $can_like;
	}

	public function getAdditionalInfo(): array
	{
		return $this->additional_info;
	}

	public function setAdditionalInfo(array $additional_info): void
	{
		$this->additional_info = $additional_info;
	}

	public static function getTypes(): array
	{
		return LikesEnum::cases();
	}

	public static function getColumns(): array
	{
		return [
			self::ID_MEMBER,
			self::TYPE,
			self::ID,
			self::TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	public function toInsert(): array
	{
		$arrayToInsert = $this->toArray();
		$arrayToInsert[LikeEntity::TYPE] = $arrayToInsert[LikeEntity::TYPE]->value;
		$arrayToInsert[LikeEntity::TIME] = $arrayToInsert[LikeEntity::TIME]->getTimestamp();

		return array_intersect_key($arrayToInsert, array_flip(LikeEntity::getColumns()));
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): mixed
	{
		return match ($columnName) {
			self::ID_MEMBER,
			self::ID, LikeEntity::COUNT => (int) $value,
			self::TIME => $value === null ? null : new DateTimeImmutable('@' . $value),
			LikeEntity::CAN_LIKE, LikeEntity::ALREADY_LIKED => (bool) $value,
			self::TYPE => is_string($value) ? LikesEnum::from($value) : $value,
			LikeEntity::ADDITIONAL_INFO => is_string($value) ? Json::decode($value) : $value,
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'contentId' => $this->getContentId(),
			'count' => $this->getCount(),
			'alreadyLiked' => $this->isAlreadyLiked(),
			'canLike' => $this->canLike(),
			'type' => $this->getContentType()->value,
			'additionalInfo' => $this->getAdditionalInfo(),
			'likeTime' => Time::from($this->getLikeTime()),
		];
	}
}
