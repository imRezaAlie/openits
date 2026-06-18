(function () {
    'use strict';

    const fetchJson = (url, options) => (window.LaravelCsrf?.fetch || fetch)(url, options);

    window.c4Collaboration = function () {
        return {
            tab: 'comments',
            comments: [],
            changeRequests: [],
            newComment: '',
            replyTo: null,
            commentTarget: null,
            crForm: { title: '', description: '', impact: '', reviewer_id: '' },

            init() {
                window.addEventListener('c4-node-selected', (e) => {
                    const node = e.detail;
                    if (!node || node.is_boundary) {
                        if (window.c4DiagramConfig?.contextId) {
                            this.commentTarget = {
                                type: 'context',
                                id: window.c4DiagramConfig.contextId,
                                name: node?.name || 'System Context',
                            };
                            this.loadComments();
                        } else {
                            this.commentTarget = null;
                            this.comments = [];
                        }
                        return;
                    }
                    const type = ['container', 'component', 'context', 'external_system', 'user'].includes(node.type)
                        ? (node.type === 'external_system' || node.type === 'user' ? 'context' : node.type)
                        : 'container';
                    const id = node.type === 'context' ? (window.c4DiagramConfig.contextId || node.id) : node.id;
                    if (type === 'context' && String(id).startsWith('system-')) {
                        this.commentTarget = {
                            type: 'context',
                            id: window.c4DiagramConfig.contextId,
                            name: node.name,
                        };
                    } else if (['container', 'component', 'context'].includes(type)) {
                        this.commentTarget = { type, id, name: node.name };
                    } else {
                        this.commentTarget = null;
                    }
                    if (this.commentTarget) this.loadComments();
                });
            },

            async loadComments() {
                if (!this.commentTarget) return;
                const cfg = window.c4DiagramConfig;
                const url = `${cfg.collaboration.comments}?element_type=${this.commentTarget.type}&element_id=${this.commentTarget.id}`;
                const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                const data = await res.json();
                this.comments = data.comments || [];
            },

            async postComment() {
                if (!this.commentTarget || !this.newComment.trim()) return;
                const cfg = window.c4DiagramConfig;
                const res = await fetchJson(cfg.collaboration.commentStore, {
                    method: 'POST',
                    body: {
                        element_type: this.commentTarget.type,
                        element_id: this.commentTarget.id,
                        body: this.newComment,
                        parent_id: this.replyTo,
                    },
                });
                if (res.ok) {
                    this.newComment = '';
                    this.replyTo = null;
                    await this.loadComments();
                } else if (res.status === 419) {
                    alert('Session expired — please refresh the page.');
                }
            },

            async toggleResolve(comment) {
                const cfg = window.c4DiagramConfig;
                await fetchJson(`${cfg.collaboration.commentResolve}/${comment.id}/resolve`, {
                    method: 'PATCH',
                    body: { resolved: !comment.resolved },
                });
                await this.loadComments();
            },

            async loadChangeRequests() {
                const cfg = window.c4DiagramConfig;
                const res = await fetch(cfg.collaboration.changeRequests, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                this.changeRequests = data.change_requests || [];
            },

            async submitChangeRequest(submit) {
                const cfg = window.c4DiagramConfig;
                const res = await fetchJson(cfg.collaboration.changeRequestStore, {
                    method: 'POST',
                    body: { ...this.crForm, submit },
                });
                if (res.ok) {
                    this.crForm = { title: '', description: '', impact: '', reviewer_id: '' };
                    await this.loadChangeRequests();
                } else if (res.status === 419) {
                    alert('Session expired — please refresh the page.');
                }
            },

            async reviewChangeRequest(id, action) {
                const notes = action === 'approve' ? '' : prompt('Reviewer notes (optional):') || '';
                const cfg = window.c4DiagramConfig;
                await fetchJson(`${cfg.collaboration.changeRequestReview}/${id}/review`, {
                    method: 'POST',
                    body: { action, reviewer_notes: notes },
                });
                await this.loadChangeRequests();
            },

            statusBadge(status) {
                return {
                    approved: 'success',
                    pending_review: 'warning',
                    rejected: 'danger',
                    changes_requested: 'info',
                    draft: 'secondary',
                }[status] || 'light';
            },
        };
    };
})();
