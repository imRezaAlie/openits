@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">User Management</h4>
                    <small class="text-muted">Create and manage application users</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" data-action="create">
                    Add User
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td class="fw-semibold">{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at?->format('M j, Y') }}</td>
                                            <td class="text-end">
                                                <button type="button"
                                                        class="btn btn-sm btn-light edit-user"
                                                        data-name="{{ $user->name }}"
                                                        data-email="{{ $user->email }}"
                                                        data-update-url="{{ route('user.update', $user) }}">
                                                    Edit
                                                </button>
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('user.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $user->name }}?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                @else
                                                    <span class="badge badge-light ms-1">You</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($users->hasPages())
                        <div class="card-footer">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" method="POST" action="{{ route('user.store') }}">
                @csrf
                <div id="userMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="user-name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="user-email" class="form-control" required value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" id="user-password-label">Password *</label>
                        <input type="password" name="password" id="user-password" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" id="user-password-confirmation-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" id="user-password-confirmation" class="form-control" autocomplete="new-password">
                        <small class="text-muted d-none" id="user-password-hint">Leave blank to keep the current password when editing.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        function setCreateMode() {
            $('#userModalTitle').text('Add User');
            $('#userForm').attr('action', '{{ route("user.store") }}');
            $('#userMethodField').html('');
            $('#userForm')[0].reset();
            $('#user-password').prop('required', true);
            $('#user-password-confirmation').prop('required', true);
            $('#user-password-label').text('Password *');
            $('#user-password-confirmation-label').text('Confirm Password *');
            $('#user-password-hint').addClass('d-none');
        }

        $('[data-action="create"]').on('click', setCreateMode);

        $('.edit-user').on('click', function() {
            const el = $(this);
            $('#userModalTitle').text('Edit User');
            $('#userForm').attr('action', el.data('update-url'));
            $('#userMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#user-name').val(el.data('name'));
            $('#user-email').val(el.data('email'));
            $('#user-password').val('').prop('required', false);
            $('#user-password-confirmation').val('').prop('required', false);
            $('#user-password-label').text('Password');
            $('#user-password-confirmation-label').text('Confirm Password');
            $('#user-password-hint').removeClass('d-none');
            new bootstrap.Modal(document.getElementById('userModal')).show();
        });

        @if($errors->any())
            new bootstrap.Modal(document.getElementById('userModal')).show();
        @endif
    </script>
@endpush
