<div class="c4-collaboration mt-4 border-top pt-3" x-data="c4Collaboration()" x-init="init()">
    <ul class="nav nav-tabs nav-tabs-sm mb-3" role="tablist">
        <li class="nav-item">
            <button type="button" class="nav-link" :class="{ active: tab === 'comments' }" @click="tab = 'comments'">Comments</button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" :class="{ active: tab === 'reviews' }" @click="tab = 'reviews'; loadChangeRequests()">Reviews</button>
        </li>
    </ul>

    <div x-show="tab === 'comments'">
        <template x-if="!commentTarget">
            <p class="text-muted small">Select a node to view or add comments.</p>
        </template>
        <template x-if="commentTarget">
            <div>
                <p class="small text-muted mb-2">Comments on <strong x-text="commentTarget.name"></strong></p>
                <div class="c4-comment-list mb-3" style="max-height: 240px; overflow-y: auto;">
                    <template x-for="c in comments" :key="c.id">
                        <div class="c4-comment mb-2 p-2 rounded" :class="c.resolved ? 'bg-light opacity-75' : 'bg-white border'">
                            <div class="d-flex justify-content-between">
                                <strong class="small" x-text="c.user?.name"></strong>
                                <span class="text-muted small" x-text="c.created_at"></span>
                            </div>
                            <p class="small mb-1" x-text="c.body"></p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-link btn-sm p-0" @click="toggleResolve(c)" x-text="c.resolved ? 'Reopen' : 'Resolve'"></button>
                                <button type="button" class="btn btn-link btn-sm p-0" @click="replyTo = c.id">Reply</button>
                            </div>
                            <template x-for="r in c.replies" :key="r.id">
                                <div class="ms-3 mt-2 p-2 border-start ps-2">
                                    <strong class="small" x-text="r.user?.name"></strong>
                                    <p class="small mb-0" x-text="r.body"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                    <p class="text-muted small" x-show="comments.length === 0">No comments yet.</p>
                </div>
                <textarea class="form-control form-control-sm mb-2" rows="3" x-model="newComment" placeholder="Add a comment… Use @[Name](id) for mentions"></textarea>
                <button type="button" class="btn btn-primary btn-sm w-100" @click="postComment()" :disabled="!newComment.trim()">Post Comment</button>
            </div>
        </template>
    </div>

    <div x-show="tab === 'reviews'">
        <div class="mb-3">
            <h6 class="small text-uppercase text-muted">Submit Change Request</h6>
            <input type="text" class="form-control form-control-sm mb-2" x-model="crForm.title" placeholder="Title">
            <textarea class="form-control form-control-sm mb-2" rows="2" x-model="crForm.description" placeholder="Describe the change"></textarea>
            <textarea class="form-control form-control-sm mb-2" rows="2" x-model="crForm.impact" placeholder="Impact assessment"></textarea>
            <select class="form-control form-control-sm mb-2" x-model="crForm.reviewer_id">
                <option value="">Assign reviewer (optional)</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="submitChangeRequest(false)">Save Draft</button>
                <button type="button" class="btn btn-primary btn-sm" @click="submitChangeRequest(true)">Submit for Review</button>
            </div>
        </div>
        <h6 class="small text-uppercase text-muted">Pending &amp; Recent</h6>
        <template x-for="cr in changeRequests" :key="cr.id">
            <div class="border rounded p-2 mb-2 small">
                <div class="d-flex justify-content-between align-items-start">
                    <strong x-text="cr.title"></strong>
                    <span class="badge" :class="'badge-' + statusBadge(cr.status)" x-text="cr.status_label"></span>
                </div>
                <p class="text-muted mb-1" x-text="cr.description"></p>
                <div class="text-muted" x-show="cr.requester">By <span x-text="cr.requester"></span> · <span x-text="cr.submitted_at"></span></div>
                <div x-show="cr.status === 'pending_review'" class="mt-2 d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-success btn-xs btn-sm" @click="reviewChangeRequest(cr.id, 'approve')">Approve</button>
                    <button type="button" class="btn btn-warning btn-xs btn-sm" @click="reviewChangeRequest(cr.id, 'request_changes')">Request Changes</button>
                    <button type="button" class="btn btn-danger btn-xs btn-sm" @click="reviewChangeRequest(cr.id, 'reject')">Reject</button>
                </div>
                <p class="mt-1 mb-0 text-muted" x-show="cr.reviewer_notes" x-text="cr.reviewer_notes"></p>
            </div>
        </template>
        <p class="text-muted small" x-show="changeRequests.length === 0">No change requests yet.</p>
    </div>
</div>
