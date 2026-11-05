<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'score', 'category', 'difficulty'])]
class Score extends Model
{
    /** @use HasFactory<ScoreFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $search = trim($search);

                $query->where(function (Builder $q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }

                    $q->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('difficulty', 'like', "%{$search}%")
                      ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%");
                      });
                });
            })
            ->when($filters['trashed'] ?? null, function (Builder $query, string $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            });
    }

}
