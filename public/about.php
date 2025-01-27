<?php
require_once "../includes/header.php";
?>

<div class="container mt-5">
    <h1><?php echo $translations['about']['title']; ?></h1>
    <p><?php echo $translations['about']['description']; ?></p>
    
    <h2><?php echo $translations['about']['team']; ?></h2>
    <ul>
        <li><?php echo $translations['about']['team_member_1']; ?></li>
        <li><?php echo $translations['about']['team_member_2']; ?></li>
        <li><?php echo $translations['about']['team_member_3']; ?></li>
    </ul>
    
    <h2><?php echo $translations['about']['mission']; ?></h2>
    <p><?php echo $translations['about']['mission_statement']; ?></p>
</div>

<?php require_once "../includes/footer.php"; ?> 