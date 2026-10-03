<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #dcd7d2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #fff;
            width: 350px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 0;
            top: 10px;
            color: green;
        }

        .input-group input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #aaa;
            padding: 10px 10px 10px 25px;
            outline: none;
            font-size: 14px;
        }

        .input-group input::placeholder {
            color: #aaa;
        }

        .forgot {
            text-align: right;
            font-size: 12px;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        .forgot a {
            text-decoration: none;
            color: #555;
        }

        .btn {
            background: green;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            float: right;
            cursor: pointer;
        }

        .btn:hover {
            background: darkgreen;
        }

        .back {
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="card">
    
    <div class="back">
        <i class="fa fa-arrow-left"></i>
    </div>

    <div class="title">Masuk</div>
    <form method="POST" action="{{ route('login') }}">
    @csrf

    @if(session('error'))
        <p style="color:red; font-size:12px;">
            {{ session('error') }}
        </p>
    @endif

    <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="email" name="email" 
               value="{{ old('email') }}"
               placeholder="nama pengguna/email" required>
    </div>

    <div class="input-group">
        <i class="fa fa-comment"></i>
        <input type="password" name="password" 
               placeholder="kata sandi" required>
    </div>

    <div class="forgot">
        <a href="#">lupa kata sandi?</a>
    </div>

    <button type="submit" class="btn">masuk</button>
</form>

    
       

    

</div>

</body>
</html>