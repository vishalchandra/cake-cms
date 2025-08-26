<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 * @var \Cake\Collection\CollectionInterface|string[] $recipientUsers
 * @var \Cake\Collection\CollectionInterface|string[] $actorUsers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Notifications'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="notifications form content">
            <?= $this->Form->create($notification) ?>
            <fieldset>
                <legend><?= __('Add Notification') ?></legend>
                <?php
                    echo $this->Form->control('recipient_user_id', ['options' => $recipientUsers]);
                    echo $this->Form->control('actor_user_id', ['options' => $actorUsers, 'empty' => true]);
                    echo $this->Form->control('type');
                    echo $this->Form->control('entity_type');
                    echo $this->Form->control('entity_id');
                    echo $this->Form->control('is_read');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
