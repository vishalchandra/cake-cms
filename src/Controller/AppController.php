<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Get current user
        $user = $this->Authentication->getIdentity();
        
        // Redirect unverified users to login (except for auth actions)
        if ($user && !$user->email_verified && $this->request->getParam('controller') !== 'Auth') {
            $this->Flash->error(__('Please verify your email address before continuing.'));
            return $this->redirect(['controller' => 'Auth', 'action' => 'login']);
        }
        
        // Make user available in templates
        $this->set('currentUser', $user);
        
        // Get unread notification count for authenticated users
        if ($user) {
            $unreadNotifications = $this->fetchTable('Notifications')->find()
                ->where([
                    'recipient_user_id' => $user->id,
                    'is_read' => false
                ])
                ->count();
            $this->set('unreadNotifications', $unreadNotifications);
        }
    }
}
