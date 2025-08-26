<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Notifications Controller
 *
 * @property \App\Model\Table\NotificationsTable $Notifications
 */
class NotificationsController extends AppController
{
    /**
     * Index method - Show current user's notifications
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $currentUser = $this->Authentication->getIdentity();
        
        // Get user's notifications with related data
        $notifications = $this->Notifications->find()
            ->contain([
                'ActorUsers' => ['fields' => ['id', 'username', 'display_name']]
            ])
            ->where(['recipient_user_id' => $currentUser->id])
            ->orderByDesc('created')
            ->limit(50)
            ->toArray();

        // Get unread count
        $unreadCount = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $currentUser->id,
                'is_read' => false
            ])
            ->count();

        // Mark all notifications as read when viewing
        $this->Notifications->updateAll(
            ['is_read' => true],
            ['recipient_user_id' => $currentUser->id, 'is_read' => false]
        );

        $this->set(compact('notifications', 'unreadCount'));
    }

    /**
     * View method
     *
     * @param string|null $id Notification id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $notification = $this->Notifications->get($id, contain: ['RecipientUsers', 'ActorUsers']);
        $this->set(compact('notification'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $notification = $this->Notifications->newEmptyEntity();
        if ($this->request->is('post')) {
            $notification = $this->Notifications->patchEntity($notification, $this->request->getData());
            if ($this->Notifications->save($notification)) {
                $this->Flash->success(__('The notification has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The notification could not be saved. Please, try again.'));
        }
        $recipientUsers = $this->Notifications->RecipientUsers->find('list', limit: 200)->all();
        $actorUsers = $this->Notifications->ActorUsers->find('list', limit: 200)->all();
        $this->set(compact('notification', 'recipientUsers', 'actorUsers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Notification id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $notification = $this->Notifications->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $notification = $this->Notifications->patchEntity($notification, $this->request->getData());
            if ($this->Notifications->save($notification)) {
                $this->Flash->success(__('The notification has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The notification could not be saved. Please, try again.'));
        }
        $recipientUsers = $this->Notifications->RecipientUsers->find('list', limit: 200)->all();
        $actorUsers = $this->Notifications->ActorUsers->find('list', limit: 200)->all();
        $this->set(compact('notification', 'recipientUsers', 'actorUsers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Notification id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $notification = $this->Notifications->get($id);
        if ($this->Notifications->delete($notification)) {
            $this->Flash->success(__('The notification has been deleted.'));
        } else {
            $this->Flash->error(__('The notification could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Mark a specific notification as read
     *
     * @param string|null $id Notification id.
     * @return \Cake\Http\Response Redirects to notification target
     */
    public function markRead($id = null)
    {
        $currentUser = $this->Authentication->getIdentity();
        
        $notification = $this->Notifications->find()
            ->where([
                'id' => $id,
                'recipient_user_id' => $currentUser->id
            ])
            ->first();

        if (!$notification) {
            $this->Flash->error(__('Notification not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Mark as read
        $notification->is_read = true;
        $this->Notifications->save($notification);

        // Redirect to the relevant content
        if ($notification->entity_type === 'post') {
            return $this->redirect(['controller' => 'Posts', 'action' => 'view', $notification->entity_id]);
        } elseif ($notification->entity_type === 'comment') {
            // Get the post ID from the comment
            $comment = $this->fetchTable('Comments')->get($notification->entity_id);
            return $this->redirect(['controller' => 'Posts', 'action' => 'view', $comment->post_id]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
