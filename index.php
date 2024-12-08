<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="../logo.png"/>
    <style>
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins",sans-serif;
}
body{
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vb;
    background-size: cover;
    background-position: center;
    background-color: gray;

}
.wrapper{
    width: 420px;
    background: transparent;
    border: 2px solid rgba(255, 255, 255,.2);
    backdrop-filter: blur(10px);
    color: white;
    border-radius: 15px;
    padding: 30px 40px;
    background-color:  black;
}
.wrapper h1{
    font-size: 36px;
    text-align: center;
}
.wrapper .input-box{
    position: relative;
    width: 100%;
    height: 50px;
    margin: 30px 0;
}
.input-box input{
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    border: 2px solid rgba(255, 255, 255,.2);
    border-radius: 40px;
    font-size: 16px;
    color: white;
    padding: 20px 45px 20px 20px;
}
.input-box input::placeholder{
    color: white;
}
.input-box i{
    position: absolute;
    right: 20px;
    top: 15px;
    transform: translate(-50%);
    font-size: 20px;
}
.wrapper .remember-forget{
   display: flex;
   justify-content: space-between;
   font-size: 14.5px;
   margin: -15px 0 15px;
}
.remember-forget label input{
    accent-color: white;
    margin-right: 3px;
}
.remember-forget a{
    color: white;
    text-decoration: none;
}
.remember-forget a:hover{
    text-decoration: underline;
}
.wrapper .btn{
   width: 100%; 
   height: 45px;
   background: white;
   border: none;
   outline: none;  
   border-radius: 40px;
   box-shadow: 0 0 10px rgba(0, 0, 0, .1);
   cursor: pointer;
   font-size: 16px;
   color: #333;
   font-weight: 600;
}
.wrapper .register-link{
   font-size: 14.5px; 
   text-align: center;
   margin: 20px 0 15px;
}
.register-link p a{
    color: white;
    text-decoration: none;
    font-weight: 600;
}
.register-link p a:hover{
    text-decoration: underline;
}
    </style>

</head>
<body>
    <div class="wrapper">
        <form action="login.php" method="POST">
            <h1>Login</h1>
            <?php
            if (isset($error)) {
                echo "<p style='color:red;'>$error</p>";
            }
            ?>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-open-alt'></i>
            </div>
            <div class="remember-forget">
                <label><input type="checkbox">Remember Me</label>
                <a href="#">Forget Password</a>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
        </form>
    </div>
</body>
</html>
