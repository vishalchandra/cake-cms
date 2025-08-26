<?php
$this->assign('title', h($post->title));

// Function to convert @mentions to links
function formatMentions($text) {
    return preg_replace('/@([a-z0-9_]{3,30})/i', '<a href="/u/$1">@$1</a>', h($text));
}
?>

<div style="margin: 2rem 0;">
    <!-- Post Content -->
    <article class="post-card">
        <div class="post-meta">
            <?= $this->Html->link(
                '@' . $post->user->username . ' (' . $post->user->display_name . ')', 
                '/u/' . $post->user->username
            ) ?> 
            • <?= $post->created->format('M j, Y \a\t g:i A') ?>
            • <?= count($post->comments) ?> <?= __n('comment', 'comments', count($post->comments)) ?>
        </div>
        
        <h1 class="post-title"><?= h($post->title) ?></h1>
        
        <div class="post-body">
            <?= nl2br(formatMentions($post->body)) ?>
        </div>
        
        <div class="post-actions">
            <?= $this->Html->link('← Back to Feed', '/', ['class' => 'btn']) ?>
            <?php if (!empty($currentUser) && $currentUser->id === $post->user_id): ?>
                <?= $this->Html->link('Edit', ['action' => 'edit', $post->id], ['class' => 'btn']) ?>
            <?php endif; ?>
        </div>
    </article>

    <!-- Comments Section -->
    <div style="margin-top: 3rem;">
        <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 0.5rem;">
            Comments (<?= count($post->comments) ?>)
        </h3>
        
        <?php if (!empty($post->comments)): ?>
            <?php foreach ($post->comments as $comment): ?>
                <div class="post-card" style="margin-left: 2rem; border-left: 3px solid #007bff;">
                    <div class="post-meta">
                        <?= $this->Html->link(
                            '@' . $comment->user->username . ' (' . $comment->user->display_name . ')', 
                            '/u/' . $comment->user->username
                        ) ?> 
                        • <?= $comment->created->timeAgoInWords() ?>
                    </div>
                    <div class="post-body">
                        <?= nl2br(formatMentions($comment->body)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666; margin: 2rem 0;">No comments yet.</p>
        <?php endif; ?>
        
        <!-- Comment Form -->
        <?php if (!empty($currentUser)): ?>
            <div id="comment-form" class="post-card" style="margin-top: 2rem; background: #f8f9fa;">
                <h4>Add a Comment</h4>
                <?= $this->Form->create($comment, [
                    'url' => '/posts/' . $post->id . '/comments/add'
                ]) ?>
                <fieldset>
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => false,
                        'placeholder' => 'Write your comment... Use @username to mention other users.',
                        'rows' => 4,
                        'maxlength' => 2000,
                        'required' => true
                    ]) ?>
                </fieldset>
                <div style="margin-top: 1rem;">
                    <?= $this->Form->button('Post Comment', ['class' => 'btn btn-success']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin: 2rem 0;">
                <?= $this->Html->link('Login to comment', '/login', ['class' => 'btn']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>