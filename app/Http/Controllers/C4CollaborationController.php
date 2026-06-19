<?php

namespace App\Http\Controllers;

use App\Models\C4ChangeRequest;
use App\Models\C4Comment;
use App\Models\System;
use App\Models\User;
use App\Services\C4CommentService;
use App\Services\C4ExportService;
use App\Services\C4VersionService;
use App\Support\C4ChangeRequestStatuses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class C4CollaborationController extends Controller
{
    public function __construct(
        private C4CommentService $commentService,
        private C4VersionService $versionService,
        private C4ExportService $exportService,
    ) {}

    public function comments(Request $request): JsonResponse
    {
        $data = $request->validate([
            'element_type' => 'required|in:container,component,context',
            'element_id' => 'required|uuid',
        ]);

        $threads = $this->commentService
            ->forElement($data['element_type'], $data['element_id'])
            ->map(fn ($c) => $this->commentService->toThreadArray($c));

        return response()->json(['comments' => $threads]);
    }

    public function storeComment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'element_type' => 'required|in:container,component,context',
            'element_id' => 'required|uuid',
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|uuid|exists:c4_comments,id',
        ]);

        $comment = $this->commentService->store(
            $data['element_type'],
            $data['element_id'],
            $request->user()->id,
            $data['body'],
            $data['parent_id'] ?? null,
        );

        return response()->json($this->commentService->toThreadArray($comment), 201);
    }

    public function resolveComment(Request $request, C4Comment $comment): JsonResponse
    {
        abort_unless(
            $request->user()->id === $comment->user_id || $request->user()->isAdmin(),
            403
        );

        $comment = $this->commentService->resolve($comment, $request->boolean('resolved', true));

        return response()->json($this->commentService->toThreadArray($comment));
    }

    public function destroyComment(Request $request, C4Comment $comment): JsonResponse
    {
        abort_unless(
            $request->user()->id === $comment->user_id || $request->user()->isAdmin(),
            403
        );

        $comment->delete();

        return response()->json(['message' => 'Comment deleted.']);
    }

    public function changeRequests(System $system): JsonResponse
    {
        $requests = $system->c4ChangeRequests()
            ->with(['requester', 'reviewer'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($cr) => $this->changeRequestArray($cr));

        return response()->json(['change_requests' => $requests]);
    }

    public function storeChangeRequest(Request $request, System $system): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'impact' => 'nullable|string',
            'reviewer_id' => 'nullable|exists:users,id',
            'submit' => 'boolean',
        ]);

        $snapshot = $this->exportService->toJson($system);

        $cr = C4ChangeRequest::create([
            'system_id' => $system->id,
            'requester_id' => $request->user()->id,
            'reviewer_id' => $data['reviewer_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'impact' => $data['impact'] ?? null,
            'status' => $request->boolean('submit')
                ? C4ChangeRequestStatuses::PENDING_REVIEW
                : C4ChangeRequestStatuses::DRAFT,
            'snapshot' => $snapshot,
            'submitted_at' => $request->boolean('submit') ? now() : null,
        ]);

        return response()->json($this->changeRequestArray($cr->load(['requester', 'reviewer'])), 201);
    }

    public function reviewChangeRequest(Request $request, C4ChangeRequest $changeRequest): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin() || $changeRequest->reviewer_id === $user->id,
            403
        );

        $data = $request->validate([
            'action' => 'required|in:approve,reject,request_changes',
            'reviewer_notes' => 'nullable|string',
        ]);

        $status = match ($data['action']) {
            'approve' => C4ChangeRequestStatuses::APPROVED,
            'reject' => C4ChangeRequestStatuses::REJECTED,
            'request_changes' => C4ChangeRequestStatuses::CHANGES_REQUESTED,
        };

        $changeRequest->update([
            'status' => $status,
            'reviewer_id' => $request->user()->id,
            'reviewer_notes' => $data['reviewer_notes'] ?? null,
            'reviewed_at' => now(),
        ]);

        if ($status === C4ChangeRequestStatuses::APPROVED) {
            $this->versionService->snapshot(
                $changeRequest->system,
                'Approved change: '.$changeRequest->title,
            );
        }

        return response()->json($this->changeRequestArray($changeRequest->fresh(['requester', 'reviewer'])));
    }

    /**
     * @return array<string, mixed>
     */
    private function changeRequestArray(C4ChangeRequest $cr): array
    {
        return [
            'id' => $cr->id,
            'title' => $cr->title,
            'description' => $cr->description,
            'impact' => $cr->impact,
            'status' => $cr->status,
            'status_label' => C4ChangeRequestStatuses::label($cr->status),
            'requester' => $cr->requester?->name,
            'reviewer' => $cr->reviewer?->name,
            'reviewer_notes' => $cr->reviewer_notes,
            'submitted_at' => $cr->submitted_at?->diffForHumans(),
            'reviewed_at' => $cr->reviewed_at?->diffForHumans(),
        ];
    }
}
