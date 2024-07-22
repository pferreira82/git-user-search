<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>GitHub User Search</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <div class="container mt-5">
            <h1>Search GitHub User</h1>

            <div id="alert-placeholder"></div>

            <form id="search-form">
                @csrf
                <div class="form-group">
                    <label for="username">GitHub Username:</label>
                    <input type="text" class="form-control" id="username" name="username">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div id="user-details" class="mt-5" style="display: none;">
                <h2>User Details</h2>
                <ul class="list-group">
                    <li class="list-group-item"><strong>GitHub Handle:</strong> <span id="user-login"></span></li>
                    <li class="list-group-item"><strong>Follower Count:</strong> <span id="user-follower-count"></span></li>
                    <li class="list-group-item"><strong>Followers:</strong>
                        <ul id="followers-list" class="list-group">
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    <script>
    $(document).ready(function() {
        $('#search-form').on('submit', function(e) {
            e.preventDefault();

            var username = $('#username').val();
            var token = $('input[name="_token"]').val();

            $.ajax({
                url: "{{ route('search-user.submit') }}",
                type: "POST",
                data: {
                    _token: token,
                    username: username
                },
                success: function(response) {
                    $('#alert-placeholder').html('');
                    $('#user-details').show();
                    $('#user-login').text(response.login);
                    $('#user-follower-count').text(response.follower_count);
                    if (response.follower_count > 0) {
                        var followersList = $('#followers-list');
                        followersList.empty();
                        response.followers.forEach(function(follower) {
                            followersList.append(
                                '<li class="list-group-item">' +
                                    '<img src="' + follower.avatar_url + '" alt="' + follower.login + '" style="width: 30px; height: 30px; margin-right: 10px; border-radius: 50%;">' +
                                    follower.login +
                                '</li>'
                            );
                        });
                    }
                    console.log(response);
                },
                error: function(xhr) {
                    $('#user-details').hide();
                    var errorMessage = xhr.responseJSON.error;
                    $('#alert-placeholder').html('<div class="alert alert-danger">' + errorMessage + '</div>');
                }
            });
        });
    });
    </script>

    </body>
</html>
