<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Notification'), ['action' => 'edit', $notification->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Notification'), ['action' => 'delete', $notification->id], ['confirm' => __('Are you sure you want to delete # {0}?', $notification->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Notifications'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Notification'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="notifications view content">
            <h3><?= h($notification->type) ?></h3>
            <table>
                <tr>
                    <th><?= __('Recipient User') ?></th>
                    <td><?= $notification->hasValue('recipient_user') ? $this->Html->link($notification->recipient_user->email, ['controller' => 'Users', 'action' => 'view', $notification->recipient_user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Actor User') ?></th>
                    <td><?= $notification->hasValue('actor_user') ? $this->Html->link($notification->actor_user->email, ['controller' => 'Users', 'action' => 'view', $notification->actor_user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= h($notification->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Entity Type') ?></th>
                    <td><?= h($notification->entity_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($notification->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Entity Id') ?></th>
                    <td><?= $this->Number->format($notification->entity_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($notification->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Read') ?></th>
                    <td><?= $notification->is_read ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>