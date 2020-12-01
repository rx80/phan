<?php

declare(strict_types=1);

namespace Phan\Language\Type;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\Type;

/**
 * Represents the PHPDoc `non-empty-mixed` type, which can cast to/from any non-null type and is non-null
 *
 * For purposes of analysis, there's usually no difference between mixed and nullable mixed.
 * @phan-pure
 */
final class NonNullMixedType extends MixedType
{
    /** @phan-override */
    public const NAME = 'non-null-mixed';

    public static function instance(bool $is_nullable)
    {
        if ($is_nullable) {
            return MixedType::instance(true);
        }
        static $instance = null;
        return $instance ?? ($instance = static::make('\\', self::NAME, [], false, Type::FROM_NODE));
    }

    public function canCastToType(Type $type): bool
    {
        return !($type instanceof NullType || $type instanceof VoidType);
    }

    /**
     * @param Type[] $target_type_set 1 or more types @phan-unused-param
     * @override
     */
    public function canCastToAnyTypeInSet(array $target_type_set): bool
    {
        foreach ($target_type_set as $t) {
            if ($this->canCastToType($t)) {
                return true;
            }
        }
        return (bool)$target_type_set;
    }

    public function asGenericArrayType(int $key_type): Type
    {
        return GenericArrayType::fromElementType($this, false, $key_type);
    }

    /**
     * @unused-param $code_base
     * @unused-param $context
     */
    public function canCastToDeclaredType(CodeBase $code_base, Context $context, Type $other): bool
    {
        return $this->canCastToType($other);
    }

    public function asObjectType(): ?Type
    {
        return ObjectType::instance(false);
    }

    public function asArrayType(): ?Type
    {
        return NonEmptyGenericArrayType::fromElementType(
            MixedType::instance(false),
            false,
            GenericArrayType::KEY_MIXED
        );
    }

    public function asNonFalseyType(): Type
    {
        return $this;
    }

    /** @override */
    public function isNullable(): bool
    {
        return $this->is_nullable;
    }

    /** @override */
    public function __toString(): string
    {
        return $this->is_nullable ? '?non-null-mixed' : 'non-null-mixed';
    }
}
