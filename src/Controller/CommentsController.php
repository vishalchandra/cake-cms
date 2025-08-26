<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Comments Controller
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Comments->find()
            ->contain(['Posts', 'Users']);
        $comments = $this->paginate($query);

        $this->set(compact('comments'));
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $comment = $this->Comments->get($id, contain: ['Posts', 'Users']);
        $this->set(compact('comment'));
    }

    /**
     * Add method - Add a comment to a specific post
     *
     * @param string|null $postId Post id.
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($postId = null)
    {
        // Verify post exists
        $post = $this->fetchTable('Posts')->get($postId);
        
        $comment = $this->Comments->newEmptyEntity();
        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            $comment->post_id = $postId;
            $comment->user_id = $this->Authentication->getIdentity()->id;
            
            if ($this->Comments->save($comment)) {
                // Process @mentions and create notifications for comments
                $this->_processMentions($comment);
                
                // Create notification for post author (if not commenting on own post)
                if ($post->user_id !== $comment->user_id) {
                    $notification = $this->fetchTable('Notifications')->newEntity([
                        'recipient_user_id' => $post->user_id,
                        'actor_user_id' => $comment->user_id,
                        'type' => 'comment_on_your_post',
                        'entity_type' => 'comment',
                        'entity_id' => $comment->id,
                    ]);
                    $this->fetchTable('Notifications')->save($notification);
                }
                
                $this->Flash->success(__('Your comment has been posted!'));
                return $this->redirect(['controller' => 'Posts', 'action' => 'view', $postId]);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        
        return $this->redirect(['controller' => 'Posts', 'action' => 'view', $postId]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $comment = $this->Comments->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $posts = $this->Comments->Posts->find('list', limit: 200)->all();
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $this->set(compact('comment', 'posts', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comments->get($id);
        if ($this->Comments->delete($comment)) {
            $this->Flash->success(__('The comment has been deleted.'));
        } else {
            $this->Flash->error(__('The comment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Process @mentions in comment content and create notifications
     */
    private function _processMentions($comment)
    {
        // Find all @mentions in the comment body
        preg_match_all('/@([a-z0-9_]{3,30})/i', $comment->body, $matches);
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
                if ($user->id === $comment->user_id) {
                    continue;
                }

                $notification = $notificationsTable->newEntity([
                    'recipient_user_id' => $user->id,
                    'actor_user_id' => $comment->user_id,
                    'type' => 'mention',
                    'entity_type' => 'comment',
                    'entity_id' => $comment->id,
                ]);

                $notificationsTable->save($notification);
            }
        }
    }
}
