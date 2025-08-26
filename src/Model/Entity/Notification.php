<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Notification Entity
 *
 * @property int $id
 * @property int $recipient_user_id
 * @property int|null $actor_user_id
 * @property string $type
 * @property string $entity_type
 * @property int $entity_id
 * @property bool $is_read
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\User $recipient_user
 * @property \App\Model\Entity\User $actor_user
 */
class Notification extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'recipient_user_id' => true,
        'actor_user_id' => true,
        'type' => true,
        'entity_type' => true,
        'entity_id' => true,
        'is_read' => true,
        'created' => true,
        'recipient_user' => true,
        'actor_user' => true,
    ];
}
