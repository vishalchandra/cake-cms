<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify your email address</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Verify your email address</h2>
        
        <p>Hi <?= h($user->display_name) ?>,</p>
        
        <p>Thank you for registering! Please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= $verifyUrl ?>" 
               style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Verify Email Address
            </a>
        </div>
        
        <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
        <p><a href="<?= $verifyUrl ?>"><?= $verifyUrl ?></a></p>
        
        <p>If you didn't create this account, you can safely ignore this email.</p>
        
        <p>Thanks,<br>
        The CMS Team</p>
    </div>
</body>
</html>