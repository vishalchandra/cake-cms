<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ? $this->fetch('title') . ' - ' : '' ?>CMS
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>
    <style>
        .header { background: #333; color: white; padding: 1rem 0; }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; }
        .header h1 { margin: 0; }
        .header h1 a { color: white; text-decoration: none; }
        .nav-links { display: flex; gap: 1rem; align-items: center; }
        .nav-links a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; }
        .nav-links a:hover { background: rgba(255,255,255,0.1); }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; color: white; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .post-card { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .post-meta { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
        .post-title { margin: 0 0 1rem 0; }
        .post-body { line-height: 1.6; margin-bottom: 1rem; }
        .post-actions { border-top: 1px solid #eee; padding-top: 1rem; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .auth-form { max-width: 400px; margin: 2rem auto; }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1><?= $this->Html->link('CMS', '/') ?></h1>
            <nav class="nav-links">
                <?php if (!empty($currentUser)): ?>
                    <?= $this->Html->link('Home', '/', ['class' => 'nav-link']) ?>
                    <?= $this->Html->link('New Post', '/posts/add', ['class' => 'btn btn-success']) ?>
                    <?= $this->Html->link('Notifications', '/notifications', ['class' => 'nav-link']) ?>
                    <?= $this->Html->link('@' . $currentUser->username, '/u/' . $currentUser->username, ['class' => 'nav-link']) ?>
                    <?= $this->Html->link('Logout', '/logout', ['class' => 'nav-link']) ?>
                <?php else: ?>
                    <?= $this->Html->link('Login', '/login', ['class' => 'btn']) ?>
                    <?= $this->Html->link('Register', '/register', ['class' => 'nav-link']) ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
