<?php
include '../common/config.php';
if(isset($_POST['username'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $res = $conn->query("SELECT * FROM admins WHERE username='$u'");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if(password_verify($p, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: index.php"); exit;
        }
    }
    $err = "Invalid credentials";
}
?>
<form method="post" style="max-width:300px; margin: 100px auto; display:flex; flex-direction:column; gap:10px;">
    <h2 style="text-align:center">Admin Login</h2>
    <input type="text" name="username" placeholder="Username" required style="padding:10px;">
    <input type="password" name="password" placeholder="Password" required style="padding:10px;">
    <button type="submit" style="padding:10px; background:blue; color:white;">Login</button>
    <?php if(isset($err)) echo "<p style='color:red'>$err</p>"; ?>
</form>
