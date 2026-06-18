<?php

namespace App\Services;

use App\Models\C4Comment;
use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class C4CommentService
{
    /**
     * @return Collection<int, C4Comment>
     */
    public function forElement(string $type, string $id): Collection
    {
        $model = $this->resolveCommentable($type, $id);

        return C4Comment::query()
            ->where('commentable_type', $model::class)
            ->where('commentable_id', $model->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function store(
        string $type,
        string $id,
        int $userId,
        string $body,
        ?string $parentId = null,
    ): C4Comment {
        $model = $this->resolveCommentable($type, $id);
        $mentions = $this->extractMentions($body);

        $comment = C4Comment::create([
            'commentable_type' => $model::class,
            'commentable_id' => $model->id,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'body' => $body,
            'mentions' => $mentions,
        ]);

        return $comment->load('user');
    }

    public function resolve(C4Comment $comment, bool $resolved = true): C4Comment
    {
        $comment->update(['resolved' => $resolved]);

        return $comment->fresh();
    }

    /**
     * @return list<int>
     */
    private function extractMentions(string $body): array
    {
        preg_match_all('/@\[([^\]]+)\]\((\d+)\)/', $body, $matches);

        return array_map('intval', $matches[2] ?? []);
    }

    public function resolveCommentable(string $type, string $id): Model
    {
        return match ($type) {
            'container' => C4Container::findOrFail($id),
            'component' => C4Component::findOrFail($id),
            'context' => C4Context::findOrFail($id),
            default => throw new \InvalidArgumentException('Invalid commentable type.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toThreadArray(C4Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'resolved' => $comment->resolved,
            'user' => $comment->user ? ['id' => $comment->user->id, 'name' => $comment->user->name] : null,
            'created_at' => $comment->created_at?->diffForHumans(),
            'replies' => $comment->replies->map(fn ($r) => $this->toThreadArray($r))->values()->all(),
        ];
    }
}
