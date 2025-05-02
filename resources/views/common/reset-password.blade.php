<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/pages-sign-in.html" />

    <title>Reset Password</title>

    <!-- <link href="css/app.css" rel="stylesheet"> -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center my-4">
                            <h1 class="h2">Reset Password</h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <x-auth-session-status class="mb-4" :status="session('status')" />
                                    <form method="POST" action="{{ route('password.store') }}">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                        <div class="mb-3">
                                            <x-input-label for="email" :value="__('Email')" />
                                            <x-input id="email" type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                                            <x-input-error :messages="$errors->get('email')" />
                                        </div>
                                        <div class="mb-3">
                                            <x-input-label for="password" :value="__('Password')" />
                                            <x-input type="password" id="password" name="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required autocomplete="new-password" />
                                            <x-input-error :messages="$errors->get('password')" />
                                        </div>
                                        <div class="mb-3">
                                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                            <x-input type="password" id="password_confirmation" name="password_confirmation" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required autocomplete="new-password" />
                                            <x-input-error :messages="$errors->get('password_confirmation')" />
                                        </div>
                                        <div>
                                            <x-button class="btn-primary w-100">Reset Password</x-button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
</body>

</html>