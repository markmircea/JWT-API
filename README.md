# QuotationFinal
 
## Table of Contents
1. [Project Overview](#project-overview)
2. [Setup Instructions](#setup-instructions)
3. [Architecture](#architecture)
4. [Authentication Flow](#authentication-flow)
5. [API Endpoints](#api-endpoints)
6. [Web Endpoints](#web-endpoints)
7. [Project Structure and Key Files](#project-structure-and-key-files)
8. [Testing](#testing)
9. [Security Considerations](#security-considerations)

## Project Overview
This project is a Laravel-based API for generating insurance quotations. It includes user authentication with JWT, token management, and quotation calculation.

## Setup Instructions

php artisan key:generate
1. Install Dependecies:
```
composer install
npm install
```
2. Generate key:
```
php artisan key:generate
```
4. Generate JWT secret key:
```
php artisan jwt:secret
```
5. Migrate the DB and run seed:
 ```
 php artisan migrate
 ```
 ```
 php artisan db:seed
 ```
6. Run
```
php artisan serve
```
```
npm run dev
```


## Architecture

The project follows a typical Laravel MVC architecture with the addition of a service layer for business logic. Key components include:

- Controllers: Handle HTTP requests and responses
- Services: Contain business logic (e.g., QuotationService)
- Models: Represent database tables and relationships
- Middleware: Handle cross-cutting concerns like authentication
- Routes: Define API and web endpoints

## Authentication Flow

###For Web:
- Login generates a refresh token as an HTTP-only cookie and redirects to the quotation page.
- The quotation page uses the refresh token to get an access token on page load, stored in memory.
- The access token is used for quotations until it expires or the page is reloaded.
- When the access token expires, it automatically refreshes using the refresh token, which is then invalidated (refresh token rotation for added security) and a new refresh token is issued, restarting the TTL.
- If the refresh fails, the user is logged out and redirected to the login page.

### For API

- Login give access token and refresh token: username and password needed
- Refresh requires refresh token sent as json, invalidates refresh token on backend for additional security
- Quotation requires access token header 
- BacklistAccess requires access token header and blacklists the current access token
- BlacklistRefresh requires the request token json and blacklist s the current refresh token



# API Endpoints

## 1. Login
- **Endpoint**: `/api/auth/login`
- **Method**: POST
- **Input**:
    ```json
    {
      "email": "user@example.com",
      "password": "password123"
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
      "token_type": "bearer",
      "access_expires_in": 3600,
      "refresh_token": "def50200641f3e1e2ffb...",
      "refresh_expires_in": 1209600
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Unauthorized"
    }
    ```

## 2. Refresh Token
- **Endpoint**: `/api/auth/refresh`
- **Method**: POST
- **Input**:
    ```json
    {
      "refresh_token": "def50200641f3e1e2ffb..."
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
      "token_type": "bearer",
      "access_expires_in": 3600,
      "refresh_token": "def50200641f3e1e2ffb...",
      "refresh_expires_in": 1209600
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Token is invalid"
    }
    ```

## 3. Blacklist Refresh Token
- **Endpoint**: `/api/auth/blacklistRefresh`
- **Method**: POST
- **Input**:
    ```json
    {
      "refresh_token": "def50200641f3e1e2ffb..."
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "message": "Refresh token invalidated successfully"
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Invalid refresh token."
    }
    ```

    ## 4. Blacklist Access Token
- **Endpoint**: `/api/auth/blacklistAccess`
- **Method**: POST
-  **Headers**:
    ```text
    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "message": "Access token invalidated successfully"
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Unauthorized."
    }
    ```

## 5. Get Quotation
- **Endpoint**: `/api/quotation`
- **Method**: POST
- **Headers**:
    ```text
    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
    ```
- **Input**:
    ```json
    {
      "age": "28,35",
      "currency_id": "EUR",
      "start_date": "2023-08-01",
      "end_date": "2023-08-30"
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "total": "117.00",
      "currency_id": "EUR",
      "quotation_id": "qid64c8a3e7b1234"
    }
    ```
- **Output (Failure - 400 Bad Request)**:
    ```json
    {
      "error": "Age must be between 18 and 70"
    }
    ```

# Web Endpoints

## 1. Show Quotation Form
- **Endpoint**: `/`
- **Method**: GET
- **Input**: None
- **Output**: HTML page with a form to input quotation details

## 2. Show Login Page
- **Endpoint**: `/login`
- **Method**: GET
- **Input**: None
- **Output**: HTML page with a login form

# Browser Authentication Endpoints

## 1. Browser Login
- **Endpoint**: `/auth/browser/login`
- **Method**: POST
- **Input**:
    ```json
    {
      "email": "user@example.com",
      "password": "password123"
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
      "token_type": "bearer",
      "access_expires_in": 3600,
      "refresh_token": "def50200641f3e1e2ffb...",
      "refresh_expires_in": 1209600
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Unauthorized"
    }
    ```

## 2. Browser Logout
- **Endpoint**: `/auth/browser/logout`
- **Method**: POST
- **Headers**:
    ```text
    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
    ```
- **Input**: None
- **Output (Success - 200 OK)**:
    ```json
    {
      "message": "Successfully logged out"
    }
    ```

## 3. Browser Refresh Token
- **Endpoint**: `/auth/browser/refresh`
- **Method**: POST
- **Input**: None (refresh token is expected to be in a cookie)
- **Output (Success - 200 OK)**:
    ```json
    {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
      "token_type": "bearer",
      "access_expires_in": 3600,
      "refresh_token": "def50200641f3e1e2ffb...",
      "refresh_expires_in": 1209600
    }
    ```
- **Output (Failure - 401 Unauthorized)**:
    ```json
    {
      "error": "Token is invalid"
    }
    ```

    ## 4. Browser Quotation
    - **Accepts the simpler version is ISO 8601 Y-m-d**
- **Endpoint**: `/quotation`
- **Method**: POST
- **Headers**:
    ```text
    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
    ```
- **Input**:
    ```json
    {
      "age": "28,35",
      "currency_id": "EUR",
      "start_date": "2023-08-01",
      "end_date": "2023-08-30"
    }
    ```
- **Output (Success - 200 OK)**:
    ```json
    {
      "total": "117.00",
      "currency_id": "EUR",
      "quotation_id": "qid64c8a3e7b1234"
    }
    ```
- **Output (Failure - 400 Bad Request)**:
    ```json
    {
      "error": "Age must be between 18 and 70"
    }
    ```


# Project Structure and Key Files

The project follows a standard Laravel structure with some additional custom components:

- `app/Http/Controllers/`
  - `APIAuthController.php`: Handles API authentication
  - `BrowserAuthController.php`: Handles browser-based authentication
  - `QuotationController.php`: Processes quotation requests
  - `QuotationFormController.php`: Handles the web form for quotations

- `app/Services/`
  - `QuotationService.php`: Contains the business logic for calculating quotations

- `routes/`
  - `api.php`: Defines API routes
  - `web.php`: Defines web routes

- `config/`
  - `jwt.php`: JWT configuration file

- `resources/views/`
  - Contains the blade templates for the web interface

- `tests/`
  - Contains unit and feature tests

Key configuration files:
- `app/Http/Kernel.php`: Defines middleware groups, including rate limiting for API routes
- `app/Providers/RouteServiceProvider.php`: Contains rate limiting configurations




# Testing
Unit tests have been set up for the QuotationController and QuotationService. To run the tests:

`php artisan test`


# Security Considerations

CSRF protection is implemented for web routes, excluding API routes.
Rate limiting is implemented for API routes to prevent abuse.
Refresh token rotation is used for enhanced security.
HTTP-only cookies are used for refresh tokens in browser authentication.
Access tokens are stored in memory (not localStorage) for browser-based access.
Failed login attempts are logged in a separate security log channel.
