<?php
$this->assign('title', 'Login');
?>
<div class="auth-form">
    <h1>Login</h1>
    
    <?= $this->Form->create() ?>
    <fieldset>
        <?= $this->Form->control('email', [
            'required' => true,
            'type' => 'email',
            'placeholder' => 'Enter your email'
        ]) ?>
        <?= $this->Form->control('password', [
            'required' => true,
            'type' => 'password',
            'placeholder' => 'Enter your password'
        ]) ?>
    </fieldset>
    
    <div class="form-actions">
        <?= $this->Form->button('Login', ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('Register', ['action' => 'register'], ['class' => 'btn btn-secondary']) ?>
    </div>
    
    <div class="form-links">
        <?= $this->Html->link('Forgot Password?', ['action' => 'forgotPassword']) ?>
    </div>
    
    <?= $this->Form->end() ?>
</div>