<?php
$this->assign('title', 'Reset Password');
?>
<div class="auth-form">
    <h1>Reset Password</h1>
    <p>Enter your new password below.</p>
    
    <?= $this->Form->create() ?>
    <fieldset>
        <?= $this->Form->control('password', [
            'required' => true,
            'type' => 'password',
            'placeholder' => 'Enter new password (min 8 characters)',
            'minlength' => 8
        ]) ?>
        <?= $this->Form->control('confirm_password', [
            'required' => true,
            'type' => 'password',
            'placeholder' => 'Confirm new password',
            'minlength' => 8
        ]) ?>
    </fieldset>
    
    <div class="form-actions">
        <?= $this->Form->button('Reset Password', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <?= $this->Form->end() ?>
</div>