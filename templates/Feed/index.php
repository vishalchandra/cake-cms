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
                        nl2br(h($post->body)), 
                        300, 
                        ['html' => true, 'exact' => false]
                    ) ?>
                </div>

                <?php if (!empty($post->comments) && count($post->comments) > 0): ?>
                    <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #007bff;">
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
                                    <?= $this->Text->truncate(h($comment->body), 100, ['exact' => false]) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($post->comments) > 3): ?>
                            <div style="margin-top: 0.5rem; text-align: right;">
                                <?= $this->Html->link(
                                    'View all ' . count($post->comments) . ' comments →', 
                                    '/posts/' . $post->id,
                                    ['style' => 'font-size: 0.9rem; color: #007bff;']
                                ) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-actions">
                    <?= $this->Html->link('Read More', '/posts/' . $post->id, ['class' => 'btn']) ?>
                    <?php if (!empty($currentUser)): ?>
                        <?= $this->Html->link('Comment', '/posts/' . $post->id . '#comment-form', ['class' => 'btn']) ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        
        <?php if (!$posts->isEmpty()): ?>
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