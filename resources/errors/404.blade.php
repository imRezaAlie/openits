
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OpenITS | Page Not Found</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link class="main-css" href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>
<div class="authincation fix-wrapper">
    <div class="container">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-6">
                <div class="error-page">
                    <div class="error-inner text-center">
                        <div class="dz-error" data-text="404">404</div>
                        <h2 class="error-head mb-0"><i class="fa fa-exclamation-triangle text-warning me-2"></i>The page you were looking for is not found!</h2>
                        <p>You may have mistyped the address or the page may have moved.</p>
                        <a href="{{ route('home') }}" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/global/global.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/deznav-init.js') }}"></script>
<script src="{{ asset('js/custom.min.js') }}"></script>
</body>
</html>
