<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PasswordReset Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Cake\I18n\DateTime $expires_at
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\User $user
 */
class PasswordReset extends Entity
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
        'user_id' => true,
        'token' => true,
        'expires_at' => true,
        'created' => true,
        'user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'token',
    ];
}
