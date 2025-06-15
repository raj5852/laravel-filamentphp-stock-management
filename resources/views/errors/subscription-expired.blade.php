<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .icon {
            color: #e53e3e;
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
        }
        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 16px;
        }
        p {
            color: #666666;
            margin-bottom: 24px;
        }
        .button-container {
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #3182ce;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin: 0 5px;
        }
        .button.primary {
            background-color: #3182ce;
        }
        .button.primary:hover {
            background-color: #2c5282;
        }
        .button.success {
            background-color: #38a169;
        }
        .button.success:hover {
            background-color: #2f855a;
        }
        .support-text {
            font-size: 14px;
            color: #888888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h1>Subscription Expired</h1>
        <p>Your subscription has expired. Please renew your subscription to continue using the service.</p>
        <div class="button-container">
            <a href="/" class="button primary">Go Home</a>
            <a href="#" class="button success">Renew Subscription</a>
        </div>
        <p class="support-text">If you believe this is an error, please contact support.</p>
    </div>
</body>
</html>