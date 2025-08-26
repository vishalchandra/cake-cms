<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PasswordResets Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\PasswordReset newEmptyEntity()
 * @method \App\Model\Entity\PasswordReset newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PasswordReset> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PasswordReset get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PasswordReset findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PasswordReset patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PasswordReset> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PasswordReset|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PasswordReset saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PasswordReset>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PasswordReset>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PasswordReset>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PasswordReset> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PasswordReset>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PasswordReset>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PasswordReset>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PasswordReset> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PasswordResetsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('password_resets');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('token')
            ->maxLength('token', 255)
            ->requirePresence('token', 'create')
            ->notEmptyString('token')
            ->add('token', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->dateTime('expires_at')
            ->requirePresence('expires_at', 'create')
            ->notEmptyDateTime('expires_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['token']), ['errorField' => 'token']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
