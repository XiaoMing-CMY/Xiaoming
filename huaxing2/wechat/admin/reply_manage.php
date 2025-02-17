<?php
session_start();
require_once '../config.php';

// 验证管理员登录状态
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 数据库连接
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 添加或更新自动回复
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keyword = $conn->real_escape_string($_POST['keyword']);
    $reply = $conn->real_escape_string($_POST['reply']);
    
    $sql = "INSERT INTO auto_replies (keyword, reply) VALUES ('$keyword', '$reply') 
            ON DUPLICATE KEY UPDATE reply = '$reply'";
    
    if ($conn->query($sql)) {
        $message = "保存成功！";
    } else {
        $error = "保存失败：" . $conn->error;
    }
}

// 获取现有回复列表
$sql = "SELECT * FROM auto_replies ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>自动回复管理 - 华兴物业公众号后台</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
        }
        .message {
            color: green;
            margin: 10px 0;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>自动回复管理</h1>
    
    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>关键词：</label>
            <input type="text" name="keyword" required>
        </div>
        
        <div class="form-group">
            <label>回复内容：</label>
            <textarea name="reply" rows="5" required></textarea>
        </div>
        
        <button type="submit">保存</button>
    </form>
    
    <h2>现有回复列表</h2>
    <table>
        <tr>
            <th>关键词</th>
            <th>回复内容</th>
            <th>操作</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['keyword']); ?></td>
            <td><?php echo htmlspecialchars($row['reply']); ?></td>
            <td>
                <a href="edit_reply.php?id=<?php echo $row['id']; ?>">编辑</a>
                <a href="delete_reply.php?id=<?php echo $row['id']; ?>" onclick="return confirm('确定要删除吗？')">删除</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html> 