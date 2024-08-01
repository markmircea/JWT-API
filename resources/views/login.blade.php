<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Login</h1>
    <form id="loginForm">
        @csrf
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <div id="result"></div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {  //listen to login submit and then post login, server sets http only cookie with refresh token, redirect to quotation page
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await axios.post('/auth/browser/login', data);

                  window.location.href = '/';

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `
                    <h2>Error</h2>
                    <p>${error.response?.data?.error || error.message}</p>
                `;
            }
        });
    </script>
</body>
</html>
