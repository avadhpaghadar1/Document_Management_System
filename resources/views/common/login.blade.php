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

	<title>Log In</title>

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

						<div class="text-center mt-4">
							<h1 class="h2">Login</h1>
							<p class="lead">
								Log in for a seamless and secure experience.
							</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-3">
									<x-auth-session-status class="mb-4" :status="session('status')" />
									<form method="POST" action="{{ route('login') }}">
										@csrf
										<div class="mb-3">
											<x-input-label for="email" :value="__('Email')" />
											<x-input type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" :value="old('email')" placeholder="Enter Email" />
											<x-input-error :messages="$errors->get('email')" />
										</div>
										<div class="mb-3">
											<x-input-label for="password" :value="__('Password')" />
											<x-input type="password" name="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter password" />
											<x-input-error :messages="$errors->get('password')" />
										</div>
										<div class="d-flex justify-content-between">
											<div class="form-check align-items-center">
												<input id="remember_me" type="checkbox" class="form-check-input" name="remember">
												<x-input-label for="remember-me" :value="__('Remember Me')" />
											</div>
											<div>
												@if (Route::has('password.request'))
												<a href="{{ route('password.request') }}" class="text-body-secondary"><u>forgot password?</u></a>
												@endif
											</div>
										</div>
										<div class="d-grid gap-2 mt-3">
											<x-button class="btn-primary">Login</x-button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="text-center mb-3">
							Don't have account? <a href="{{ route('register') }}">Register</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="js/app.js"></script>

</body>

</html>