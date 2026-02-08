<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>500 - Internal Server Error</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }
        .error-box {
            background: #fff;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-width: 420px;
        }
        h1 {
            font-size: 64px;
            margin: 0;
            color: #f39c12; /* amber/orange for server error */
        }
        h2 {
            margin: 10px 0;
        }
        p {
            font-size: 15px;
            color: #666;
            line-height: 1.5;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #f39c12;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background: #d68910;
        }
        .hint {
            margin-top: 12px;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>500</h1>
        <h2>Internal Server Error</h2>
        <p>
            Something went wrong on our side.<br>
            Please try again in a moment.
        </p>
        <a href="<?= base_url() ?>">Go to Home</a>
        <div class="hint">
            If the problem persists, contact support.
        </div>
    </div>
</body>
</html>
