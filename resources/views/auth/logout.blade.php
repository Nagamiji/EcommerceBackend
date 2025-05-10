<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logging Out</title>
</head>
<body>
    <script>
        const token = localStorage.getItem('token');
        if (token) {
            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Logout Response:', data);
                localStorage.removeItem('token');
                window.location.href = '{{ route('login') }}';
            })
            .catch(error => {
                console.error('Error during logout:', error);
                localStorage.removeItem('token');
                window.location.href = '{{ route('login') }}';
            });
        } else {
            window.location.href = '{{ route('login') }}';
        }
    </script>
</body>
</html>