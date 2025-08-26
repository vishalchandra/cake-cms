<?php
$this->assign('title', 'Register');
?>
<div class="auth-form">
    <h1>Register</h1>
    
    <?= $this->Form->create($user) ?>
    <fieldset>
        <?= $this->Form->control('email', [
            'required' => true,
            'type' => 'email',
            'placeholder' => 'Enter your email'
        ]) ?>
        <?= $this->Form->control('username', [
            'required' => true,
            'placeholder' => 'Choose a username (lowercase, numbers, underscores only)',
            'pattern' => '[a-z0-9_]{3,30}',
            'title' => 'Username must be 3-30 characters: lowercase letters, numbers, and underscores only'
        ]) ?>
        <?= $this->Form->control('display_name', [
            'required' => true,
            'placeholder' => 'Enter your display name'
        ]) ?>
        <?= $this->Form->control('password', [
            'required' => true,
            'type' => 'password',
            'placeholder' => 'Enter a secure password (min 8 characters)',
            'minlength' => 8
        ]) ?>
        <?= $this->Form->control('confirm_password', [
            'required' => true,
            'type' => 'password',
            'placeholder' => 'Confirm your password',
            'minlength' => 8
        ]) ?>
    </fieldset>
    
    <div class="form-actions">
        <?= $this->Form->button('Register', ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('Login', ['action' => 'login'], ['class' => 'btn btn-secondary']) ?>
    </div>
    
    <?= $this->Form->end() ?>
</div>