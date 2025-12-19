<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;
use JsonSerializable;

class LikeInfoEntity extends Entity implements JsonSerializable
{
	public const string LIKES = 'likes';
	public const string TEXT = 'text';
	public const string HREF = 'href';

	/** @var LikeInfoEntity[] */
	public array $likes = [];

	protected int $content_id = 0;

	protected string $text = '';

	protected string $href = '';

	protected bool $can_like = false;

	protected bool $already_liked = false;

	protected LikesEnum $type;

	public function setContentId(int $contentId): void
	{
		$this->content_id = $contentId;
	}

	public function getContentId(): int
	{
		return $this->content_id;
	}

	public function setLikes(array $likes): void
	{
		$this->likes = $likes;
	}

	public function pushLike(LikeEntity $like): void
	{
		$this->likes[] = $like;
	}

	public function getLikes(): array
	{
		return $this->likes;
	}

	public function setText(string $text): void
	{
		$this->text = $text;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setHref(string $href): void
	{
		$this->href = $href;
	}

	public function getHref(): string
	{
		return $this->href;
	}

	public function setCanLike(bool $canLike): void
	{
		$this->can_like = $canLike;
	}

	public function canLike(): bool
	{
		return $this->can_like;
	}

	public function setAlreadyLiked(bool $alreadyLiked): void
	{
		$this->already_liked = $alreadyLiked;
	}

	public function isAlreadyLiked(): bool
	{
		return $this->already_liked;
	}

	public function setContentType(LikesEnum $type): void
	{
		$this->type = $type;
	}

	public function getContentType(): LikesEnum
	{
		return $this->type;
	}

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public static function getColumns(): array
	{
		return [];
	}

	public static function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function toInsert(): array
	{
		return [];
	}

	public function castValue(string $columnName, mixed $value): string|array|int|bool|LikesEnum
	{
		return match ($columnName) {
			self::LIKES => array_map(function ($like) {
				return is_array($like) ? LikeEntity::from($like) : $like;
			}, $value),
			LikeEntity::ID, LikeEntity::COUNT => (int) $value,
			LikeEntity::CAN_LIKE, LikeEntity::ALREADY_LIKED => (bool) $value,
			LikeEntity::TYPE => LikesEnum::from($value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'likes' => $this->getLikes(),
			'count' => count($this->getLikes()),
			'contentId' => $this->getContentId(),
			'text' => $this->getText(),
			'href' => $this->getHref(),
			'canLike' => $this->canLike(),
			'alreadyLiked' => $this->isAlreadyLiked(),
			'type' => $this->getContentType()->value,
		];
	}
}
