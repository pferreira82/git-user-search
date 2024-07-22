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
                    <li class="list-group-item" style="display: none">Next Page<span id="user-follower-next-page"></span></li>
                    <li class="list-group-item"><strong>Followers:</strong>
                        <ul id="followers-list" class="list-group">
                        </ul>
                        <button id="load-more" class="btn btn-secondary mt-3" style="display: none;">Load More</button>
                    </li>
                </ul>
            </div>
        </div>
    <script>
    $(document).ready(function() {
        var nextPage = 2;

        function loadFollowers(url, append = false) {
            var token = $('input[name="_token"]').val();
            var username = $('#username').val();

            $.ajax({
                url: url,
                type: "POST",
                // headers: {
                //     'X-Custom-Header': 'YourCustomHeaderValue'
                // },
                data: {
                    _token: token,
                    username: username,
                    nextPage: nextPage
                },
                success: function(response) {
                    var followersList = $('#followers-list');

                    if (!append) {
                        followersList.empty(); // Clear the previous list if not appending
                    }
                    response.followers_list.forEach(function(follower) {
                        followersList.append(
                            '<li class="list-group-item">' +
                                '<img src="' + follower.avatar_url + '" alt="' + follower.login + '" style="width: 30px; height: 30px; margin-right: 10px; border-radius: 50%;">' +
                                follower.login +
                            '</li>'
                        );
                    });
                    // Update nextPage if provided
                    if (response.next_page) {
                        nextPage = response.next_page;
                        $('#load-more').show();
                    } else {
                        $('#load-more').hide();
                    }
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON.error;
                    $('#alert-placeholder').html('<div class="alert alert-danger">' + errorMessage + '</div>');
                }
            });
        }

        $('#search-form').on('submit', function(e) {
            e.preventDefault();

            // Reset data on new search
            $('#alert-placeholder').html('');
            $('#user-details').hide();
            $('#followers-list').empty();
            $('#load-more').hide();
            $('#user-follower-count').empty();
            $('#user-login').empty();

            var username = $('#username').val();
            var token = $('input[name="_token"]').val();
            var nextPage = 2;

            $.ajax({
                cache: false,
                url: "{{ route('search-user.submit') }}",
                type: "POST",
                data: {
                    _token: token,
                    username: username,
                    nextPage: nextPage
                },
                success: function(response) {
                    $('#alert-placeholder').html('');
                    $('#user-details').show();
                    $('#user-login').text(response.login);
                    $('#user-follower-count').text(response.follower_count);
                    $('#user-follower-next-page').text(response.next_page);

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
                    if (response.next_page > 1) {
                        $('#load-more').show();
                    }
                },
                error: function(xhr) {
                    $('#user-details').hide();
                    var errorMessage = xhr.responseJSON.error;
                    $('#alert-placeholder').html('<div class="alert alert-danger">' + errorMessage + '</div>');
                }
            });
        });

        $('#load-more').on('click', function() {
            loadFollowers("{{ route('load-more-followers') }}", true);
        });
    });
    </script>

    </body>
</html>
