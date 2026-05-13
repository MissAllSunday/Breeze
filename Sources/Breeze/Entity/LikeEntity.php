<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\Enums\LikesEnum;
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

	public int $id_member = 0;

	protected LikesEnum $content_type;

	public int $content_id = 0;

	protected ?DateTimeImmutable $like_time = null;

	protected array $userData = [];

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

	public function getLikeTime(): ?DateTimeImmutable
	{
		return $this->like_time;
	}

	public function setLikeTime(?DateTimeImmutable $time): void
	{
		$this->like_time = $time;
	}

	public function setUserData(array $userData): void
	{
		$this->userData = $userData;
	}

	public function getUserData(): array
	{
		return $this->userData;
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
		$this->setLikeTime(new DateTimeImmutable);
		$arrayToInsert = $this->toArray();
		$arrayToInsert[LikeEntity::TYPE] = $arrayToInsert[LikeEntity::TYPE]->value;

		return array_intersect_key($arrayToInsert, array_flip(LikeEntity::getColumns()));
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int|LikesEnum|DateTimeImmutable
	{
		return match ($columnName) {
			self::ID_MEMBER,
			self::ID => (int) $value,
			self::TIME => new DateTimeImmutable('@' . $value),
			self::TYPE => LikesEnum::from($value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'contentId' => $this->getContentId(),
			'type' => $this->getContentType()->value,
			'likeTime' => Time::from($this->getLikeTime()),
			'idMember' => $this->getIdMember(),
			'userData' => $this->getUserData(),
		];
	}
}
