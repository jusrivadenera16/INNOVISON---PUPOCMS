<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ExcludesInactiveUserRecords
{
    public static function bootExcludesInactiveUserRecords(): void
    {
        static::addGlobalScope('activeLinkedUser', function (Builder $builder): void {
            $model = $builder->getModel();
            $foreignKey = $model->getInactiveUserForeignKey();
            $qualifiedForeignKey = $model->qualifyColumn($foreignKey);
            $userRelation = $model->getInactiveUserRelation();

            $builder->where(function (Builder $query) use ($qualifiedForeignKey, $userRelation): void {
                $query->whereNull($qualifiedForeignKey)
                    ->orWhereHas($userRelation, function (Builder $userQuery): void {
                        $userQuery->visibleForAdminHubRecords();
                    });
            });
        });
    }

    public function getInactiveUserForeignKey(): string
    {
        return 'user_id';
    }

    public function getInactiveUserRelation(): string
    {
        return 'user';
    }
}
