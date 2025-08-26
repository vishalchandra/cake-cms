<?php
$this->assign('title', '@' . $user->username);

// Function to convert @mentions to links
function formatMentions($text) {
    return preg_replace('/@([a-z0-9_]{3,30})/i', '<a href="/u/$1">@$1</a>', h($text));
}
?>

<div style="margin: 2rem 0;">
    <!-- User Profile Header -->
    <div class="post-card" style="text-align: center; margin-bottom: 2rem;">
        <div style="padding: 2rem;">
            <h1 style="margin: 0; font-size: 2.5rem; color: #333;">@<?= h($user->username) ?></h1>
            <h2 style="margin: 0.5rem 0; font-size: 1.5rem; color: #666; font-weight: normal;"><?= h($user->display_name) ?></h2>
            
            <div style="margin-top: 1.5rem; color: #999; display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <strong><?= $postCount ?></strong> <?= __n('post', 'posts', $postCount) ?>
                </div>
                <div>
                    <strong><?= $commentCount ?></strong> <?= __n('comment', 'comments', $commentCount) ?>
                </div>
                <div>
                    Joined <?= $joinDate->format('M Y') ?>
                </div>
            </div>
            
            <?php if (!empty($currentUser) && $currentUser->id === $user->id): ?>
                <div style="margin-top: 1rem;">
                    <span style="padding: 0.5rem 1rem; background: #e9ecef; color: #495057; border-radius: 20px; font-size: 0.9rem;">
                        This is your profile
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User's Posts -->
    <div style="margin-bottom: 2rem;">
        <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 0.5rem; margin-bottom: 2rem;">
            Posts by @<?= h($user->username) ?> (<?= $postCount ?>)
        </h3>

        <?php if ($posts->isEmpty()): ?>
            <div class="post-card" style="text-align: center; padding: 3rem; color: #666;">
                <p style="font-size: 1.2rem; margin: 0;">@<?= h($user->username) ?> hasn't posted anything yet.</p>
                <?php if (!empty($currentUser) && $currentUser->id === $user->id): ?>
                    <p style="margin-top: 1rem;">
                        <?= $this->Html->link('Create your first post!', '/posts/add', ['class' => 'btn btn-success']) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="post-meta">
                        <?= $post->created->timeAgoInWords() ?>
                        • <?= count($post->comments) ?> <?= __n('comment', 'comments', count($post->comments)) ?>
                    </div>
                    
                    <h3 class="post-title">
                        <?= $this->Html->link(
                            h($post->title), 
                            '/posts/' . $post->id
                        ) ?>
                    </h3>
                    
                    <div class="post-body">
                        <?= $this->Text->truncate(
                            nl2br(formatMentions($post->body)), 
                            300, 
                            ['html' => true, 'exact' => false]
                        ) ?>
                    </div>

                    <?php if (!empty($post->comments) && count($post->comments) > 0): ?>
                        <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #28a745;">
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                                Recent comments:
                            </div>
                            <?php foreach (array_slice($post->comments, 0, 3) as $comment): ?>
                                <div style="margin-bottom: 0.5rem; padding-bottom: 0.5rem; <?= $comment !== end(array_slice($post->comments, 0, 3)) ? 'border-bottom: 1px solid #dee2e6;' : '' ?>">
                                    <span style="font-weight: bold; color: #333;">
                                        <?= $this->Html->link('@' . $comment->user->username, '/u/' . $comment->user->username) ?>
                                    </span>
                                    <span style="color: #666; font-size: 0.8rem;">
                                        • <?= $comment->created->timeAgoInWords() ?>
                                    </span>
                                    <div style="margin-top: 0.2rem; color: #333;">
                                        <?= $this->Text->truncate(formatMentions($comment->body), 100, ['html' => true, 'exact' => false]) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-actions">
                        <?= $this->Html->link('Read More', '/posts/' . $post->id, ['class' => 'btn']) ?>
                        <?php if (!empty($currentUser)): ?>
                            <?= $this->Html->link('Comment', '/posts/' . $post->id . '#comment-form', ['class' => 'btn']) ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($currentUser) && ($currentUser->id === $post->user_id || $currentUser->role === 'admin')): ?>
                            <?= $this->Html->link('Edit', '/posts/edit/' . $post->id, ['class' => 'btn']) ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            
            <?php if ($postCount > 10): ?>
                <div style="margin-top: 3rem; text-align: center;">
                    <div class="pagination" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                        <?= $this->Paginator->first('« First', ['class' => 'btn']) ?>
                        <?= $this->Paginator->prev('‹ Previous', ['class' => 'btn']) ?>
                        <?= $this->Paginator->numbers(['class' => 'btn']) ?>
                        <?= $this->Paginator->next('Next ›', ['class' => 'btn']) ?>
                        <?= $this->Paginator->last('Last »', ['class' => 'btn']) ?>
                    </div>
                    <div style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                        <?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} posts out of {{count}} total') ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Back to Feed -->
    <div style="text-align: center; margin-top: 3rem;">
        <?= $this->Html->link('← Back to Feed', '/', ['class' => 'btn']) ?>
    </div>
</div>