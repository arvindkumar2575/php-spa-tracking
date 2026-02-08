<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>401 - Unauthorized</title>
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
            color: #f39c12;
        }
        h2 {
            margin: 10px 0;
        }
        p {
            font-size: 15px;
            color: #666;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>401</h1>
        <h2>Unauthorized</h2>
        <p>You are not authorized to access this page.</p>
        <a href="<?= base_url() ?>">Go to Home</a>
    </div>
</body>
</html>
