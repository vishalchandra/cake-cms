<?php
$this->assign('title', 'Create New Post');
?>

<div class="form-container" style="margin: 2rem auto;">
    <h1>Create New Post</h1>
    
    <?= $this->Form->create($post) ?>
    <fieldset>
        <?= $this->Form->control('title', [
            'required' => true,
            'placeholder' => 'Enter post title (max 160 characters)',
            'maxlength' => 160
        ]) ?>
        
        <?= $this->Form->control('body', [
            'type' => 'textarea',
            'required' => true,
            'placeholder' => 'Write your post here... Use @username to mention other users.',
            'rows' => 10,
            'maxlength' => 10000,
            'style' => 'height: 200px;'
        ]) ?>
    </fieldset>
    
    <div class="form-actions" style="margin-top: 2rem;">
        <?= $this->Form->button('Publish Post', ['class' => 'btn btn-success']) ?>
        <?= $this->Html->link('Cancel', '/', ['class' => 'btn']) ?>
    </div>
    
    <?= $this->Form->end() ?>
    
    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
        <h4>Formatting Tips:</h4>
        <ul>
            <li>Use <strong>@username</strong> to mention other users - they'll get notified!</li>
            <li>Keep your title concise (160 characters max)</li>
            <li>Post body supports up to 10,000 characters</li>
        </ul>
    </div>
</div>
