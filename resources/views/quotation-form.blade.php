<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Form</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Quotation Form</h1>
    <button id="logoutButton">Logout</button>
    <form id="quotationForm">
        @csrf
        <div><label for="age">Ages (comma-separated):</label><input type="text" id="age" name="age" required></div>
        <div><label for="currency_id">Currency:</label><select id="currency_id" name="currency_id" required>
            <option value="EUR">EUR</option><option value="GBP">GBP</option><option value="USD">USD</option>
        </select></div>
        <div><label for="start_date">Start Date:</label><input type="date" id="start_date" name="start_date" required></div>
        <div><label for="end_date">End Date:</label><input type="date" id="end_date" name="end_date" required></div>
        <button type="submit">Get Quotation</button>
    </form>
    <div id="result"></div>
    <script>
        let api;

        document.getElementById('logoutButton').addEventListener('click', logout);

        document.addEventListener('DOMContentLoaded', init);

        async function init() {  //request access token and save to memory with refresh cookie
            try {
                await refreshTokens();
                setupFormSubmission();
            } catch (error) {
                console.error('Initialization failed:', error);
                handleError(error);
                window.location.href = '/login';
            }
        }



        function setupApi(access_token) { // set headers and api interceptor to handle expired access token 401 and refresh
            api = axios.create({
                baseURL: '',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${access_token}`
                }
            });

            api.interceptors.response.use( //intercept form submission and refresh access token if neccessary
                response => response,
                async error => {
                    const originalRequest = error.config;
                    if (error.response && error.response.status === 401 && !originalRequest._retry) { //if unauthorized then refresh access token
                        originalRequest._retry = true;
                        try {
                            await refreshTokens();
                            return api(originalRequest);
                        } catch (refreshError) {
                            console.error('Token refresh error:', refreshError);
                            handleError(refreshError);
                            await logout();
                            return Promise.reject(refreshError);
                        }
                    }
                    return Promise.reject(error);
                }
            );
        }

        async function refreshTokens() {
            try {
                const response = await axios.post('/auth/browser/refresh'); //post refresh returns new access token
                setupApi(response.data.access_token); // get and set access token to api headers
            } catch (error) {
                console.error('Token refresh failed:', error);
                handleError(error);
                throw error;
            }
        }

        function setupFormSubmission() {  //eventlsitner form submission + response
            document.getElementById('quotationForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                try {
                    const response = await api.post('/quotation', Object.fromEntries(new FormData(e.target)));
                    document.getElementById('result').innerHTML = `
                        <h2>Quotation Result</h2>
                        <p>Total: ${response.data.total} ${response.data.currency_id}</p>
                        <p>Quotation ID: ${response.data.quotation_id}</p>
                    `;
                } catch (error) {
                    console.error('Error:', error);
                    handleError(error);
                }
            });
        }

        async function logout() { //server logout req + redirect
            try {
                await api.post('/auth/browser/logout');
            } catch (error) {
                console.error('Logout error:', error);
                handleError(error);
            } finally {
                window.location.href = '/login';
            }
        }

        function handleError(error) {  //error handling
            let errorMessage = 'An unexpected error occurred. Please try again.';
            if (error.response) {
                errorMessage = error.response.data.error || error.response.data.message || errorMessage;
            } else if (error.request) {
                errorMessage = 'No response received from server. Please check your internet connection.';
            }
            document.getElementById('result').innerHTML = `<h2>Error</h2><p>${errorMessage}</p>`;
        }
    </script>
</body>
</html>
