<?php
$this->assign('title', 'Home');
?>

<div style="margin: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Recent Posts</h2>
        <?php if (!empty($currentUser)): ?>
            <?= $this->Html->link('Create New Post', '/posts/add', ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </div>

    <?php if ($posts->isEmpty()): ?>
        <div class="post-card">
            <p>No posts yet. <?= $this->Html->link('Be the first to post!', '/posts/add') ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div class="post-meta">
                    <?= $this->Html->link(
                        '@' . $post->user->username, 
                        '/u/' . $post->user->username
                    ) ?> 
                    • <?= $post->created->timeAgoInWords() ?>
                    • <?= $post->comment_count ?> <?= __n('comment', 'comments', $post->comment_count) ?>
                </div>
                
                <h3 class="post-title">
                    <?= $this->Html->link(
                        h($post->title), 
                        '/posts/' . $post->id
                    ) ?>
                </h3>
                
                <div class="post-body">
                    <?= $this->Text->truncate(
                        nl2br(h($post->body)), 
                        300, 
                        ['html' => true, 'exact' => false]
                    ) ?>
                </div>
                
                <div class="post-actions">
                    <?= $this->Html->link('Read More', '/posts/' . $post->id, ['class' => 'btn']) ?>
                    <?php if (!empty($currentUser)): ?>
                        <?= $this->Html->link('Comment', '/posts/' . $post->id . '#comment-form', ['class' => 'btn']) ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>