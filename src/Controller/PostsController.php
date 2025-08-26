<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Posts Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Posts->find()
            ->contain(['Users']);
        $posts = $this->paginate($query);

        $this->set(compact('posts'));
    }

    /**
     * View method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $post = $this->Posts->get($id, contain: [
            'Users' => ['fields' => ['id', 'username', 'display_name']],
            'Comments' => [
                'Users' => ['fields' => ['id', 'username', 'display_name']],
                'sort' => ['Comments.created' => 'ASC']
            ]
        ]);
        
        // Create new comment entity for the form
        $comment = $this->fetchTable('Comments')->newEmptyEntity();
        
        $this->set(compact('post', 'comment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $post = $this->Posts->newEmptyEntity();
        if ($this->request->is('post')) {
            $post = $this->Posts->patchEntity($post, $this->request->getData());
            $post->user_id = $this->Authentication->getIdentity()->id;
            
            if ($this->Posts->save($post)) {
                // Parse @mentions and create notifications
                $this->_processMentions($post);
                
                $this->Flash->success(__('Your post has been published!'));
                return $this->redirect(['controller' => 'Posts', 'action' => 'view', $post->id]);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $this->set(compact('post'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $post = $this->Posts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $post = $this->Posts->patchEntity($post, $this->request->getData());
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('The post has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $users = $this->Posts->Users->find('list', limit: 200)->all();
        $this->set(compact('post', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $post = $this->Posts->get($id);
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The post has been deleted.'));
        } else {
            $this->Flash->error(__('The post could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Process @mentions in post content and create notifications
     */
    private function _processMentions($post)
    {
        // Find all @mentions in the post body
        preg_match_all('/@([a-z0-9_]{3,30})/i', $post->body, $matches);
        $mentionedUsernames = array_unique($matches[1]);

        if (!empty($mentionedUsernames)) {
            // Find users by username
            $mentionedUsers = $this->fetchTable('Users')->find()
                ->where(['username IN' => $mentionedUsernames, 'email_verified' => true])
                ->toArray();

            // Create notifications for mentioned users
            $notificationsTable = $this->fetchTable('Notifications');
            foreach ($mentionedUsers as $user) {
                // Don't notify if user mentioned themselves
                if ($user->id === $post->user_id) {
                    continue;
                }

                $notification = $notificationsTable->newEntity([
                    'recipient_user_id' => $user->id,
                    'actor_user_id' => $post->user_id,
                    'type' => 'mention',
                    'entity_type' => 'post',
                    'entity_id' => $post->id,
                ]);

                $notificationsTable->save($notification);
            }
        }
    }
}
