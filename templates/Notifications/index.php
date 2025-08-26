<?php
$this->assign('title', 'Notifications');

function formatNotificationMessage($notification) {
    $actorName = $notification->actor_user ? '@' . $notification->actor_user->username : 'Someone';
    
    switch ($notification->type) {
        case 'mention':
            return $actorName . ' mentioned you in a ' . $notification->entity_type;
        case 'comment_on_your_post':
            return $actorName . ' commented on your post';
        default:
            return 'New notification from ' . $actorName;
    }
}
?>

<div style="margin: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Notifications</h1>
        <?php if ($unreadCount > 0): ?>
            <span style="background: #dc3545; color: white; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.9rem;">
                <?= $unreadCount ?> new
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="post-card" style="text-align: center; padding: 3rem; color: #666;">
            <h3 style="margin: 0;">No notifications yet!</h3>
            <p style="margin-top: 1rem;">You'll see notifications here when:</p>
            <ul style="list-style: none; padding: 0; margin-top: 1rem;">
                <li>• Someone mentions you with @<?= $currentUser->username ?></li>
                <li>• Someone comments on your posts</li>
            </ul>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="post-card" style="border-left: 3px solid <?= $notification->is_read ? '#28a745' : '#007bff' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-weight: <?= $notification->is_read ? 'normal' : 'bold' ?>;">
                                <?= formatNotificationMessage($notification) ?>
                            </span>
                            <?php if (!$notification->is_read): ?>
                                <span style="background: #007bff; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.8rem; margin-left: 0.5rem;">
                                    NEW
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="color: #666; font-size: 0.9rem;">
                            <?= $notification->created->timeAgoInWords() ?>
                        </div>
                    </div>
                    
                    <div style="margin-left: 1rem;">
                        <?= $this->Html->link(
                            'View', 
                            ['action' => 'markRead', $notification->id],
                            ['class' => 'btn']
                        ) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($notifications) >= 50): ?>
            <div style="text-align: center; margin-top: 2rem; color: #666;">
                <p>Showing recent 50 notifications</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 3rem;">
        <?= $this->Html->link('← Back to Feed', '/', ['class' => 'btn']) ?>
    </div>
</div>