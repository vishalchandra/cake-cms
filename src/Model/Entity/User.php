<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $email
 * @property bool $email_verified
 * @property string|null $email_verification_token
 * @property string $password_hash
 * @property string $username
 * @property string $display_name
 * @property string $role
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\PasswordReset[] $password_resets
 * @property \App\Model\Entity\Post[] $posts
 */
class User extends Entity
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
        'email' => true,
        'email_verified' => true,
        'email_verification_token' => true,
        'password_hash' => true,
        'username' => true,
        'display_name' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
        'comments' => true,
        'password_resets' => true,
        'posts' => true,
    ];
}
