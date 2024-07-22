<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>GitHub User Search</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <div class="container mt-5">
               @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('search-user.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="username">GitHub Username:</label>
                    <input type="text" class="form-control @if(session('error')) is-invalid @endif" id="username" name="username" required>
                    @if(session('error'))
                        <div class="invalid-feedback">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            @if (isset($user))
                <div class="mt-5">
                    <h2>User Details</h2>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Username:</strong> {{ $user['login'] }}</li>
                    </ul>
                </div>
            @endif
        </div>
    </body>
</html>
