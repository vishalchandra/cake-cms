<?php
$this->assign('title', 'Forgot Password');
?>
<div class="auth-form">
    <h1>Forgot Password</h1>
    <p>Enter your email address and we'll send you a link to reset your password.</p>
    
    <?= $this->Form->create() ?>
    <fieldset>
        <?= $this->Form->control('email', [
            'required' => true,
            'type' => 'email',
            'placeholder' => 'Enter your email address'
        ]) ?>
    </fieldset>
    
    <div class="form-actions">
        <?= $this->Form->button('Send Reset Link', ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('Back to Login', ['action' => 'login'], ['class' => 'btn btn-secondary']) ?>
    </div>
    
    <?= $this->Form->end() ?>
</div>