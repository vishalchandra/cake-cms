Got you. The trick is to give Claude a single, concrete SPEC.md in your repo root, then drive it in small, reviewable tasks (“implement step 1, show diffs, run tests”). Below is a ready-to-paste spec and a kickoff prompt you can hand to Claude Code in PhpStorm.

⸻

SPEC.md (paste this at your repo root)

1) Overview

Build a multi-user mini-CMS in CakePHP with:
•	Email+password auth (with email verification + password reset).
•	User profiles at /u/:username.
•	Users can create posts (title + body; optional image later).
•	Other users can comment on posts.
•	A common feed at / (reverse-chronological list of all posts).
•	@mentions (e.g., @alice) in post bodies and comments → create notifications for tagged users.
•	MySQL for persistence. Migrations for schema, seeds for demo data.
•	Role: user (default), admin.

Non-goals (for v1): WYSIWYG, uploads, hashtags, likes, follows.

2) Stack & conventions
   •	CakePHP 5.x, PHP 8.2+, MySQL 8.
   •	Auth: cakephp/authentication + cakephp/authorization.
   •	Migrations: cakephp/migrations.
   •	Emails via Mailpit (dev) and SMTP (prod) using env vars.
   •	DDEV: docroot is webroot. CLI via ddev exec.

3) Data model (ERD)

Users 1───* Posts
Users 1───* Comments
Posts 1───* Comments
Users 1───* Notifications (as recipient)
Users 1───* Notifications (as actor)  [nullable]

Tables & indexes

users
•	id PK
•	email (unique), email_verified boolean, email_verification_token nullable
•	password_hash
•	username (unique, slug-safe [a-z0-9_]{3,30})
•	display_name
•	role ENUM(‘user’,‘admin’) default ‘user’
•	created, modified
Indexes: unique(email), unique(username)

posts
•	id PK
•	user_id FK users.id
•	title varchar(160)
•	body text
•	created, modified
Indexes: idx_posts_user_created (user_id, created DESC)

comments
•	id PK
•	post_id FK posts.id
•	user_id FK users.id
•	body text
•	created, modified
Indexes: idx_comments_post_created (post_id, created DESC)

notifications
•	id PK
•	recipient_user_id FK users.id
•	actor_user_id FK users.id nullable
•	type ENUM(‘mention’,‘comment_on_your_post’)
•	entity_type ENUM(‘post’,‘comment’)
•	entity_id bigint
•	is_read boolean default false
•	created
Indexes: idx_notifications_recipient_created (recipient_user_id, created DESC), idx_notifications_unread (recipient_user_id, is_read)

password_resets
•	id PK
•	user_id FK users.id
•	token (unique)
•	expires_at datetime
•	created
Indexes: unique(token), idx_password_resets_user_expires (user_id, expires_at)

4) Routes (HTTP)
   •	GET / → FeedController::index (list posts w/ author & comment counts)
   •	GET /login, POST /login
   •	GET /register, POST /register
   •	POST /logout
   •	GET /verify?token=...
   •	GET /password/forgot, POST /password/forgot
   •	GET /password/reset?token=..., POST /password/reset
   •	GET /u/:username → ProfilesController::view
   •	GET /posts/add, POST /posts/add
   •	GET /posts/:id → PostsController::view (show + comments)
   •	POST /posts/:id/comments/add
   •	GET /notifications (list), POST /notifications/:id/read

5) AuthN & AuthZ
   •	Authentication: identify by email, password hashed with DefaultPasswordHasher.
   •	Registration flow: create user (email_verified=false) → email verification token → send link → verify sets email_verified=true.
   •	Password reset: token w/ 1-hour TTL.
   •	Authorization:
   •	Users can edit/delete their own posts/comments; admins can manage all.
   •	Only logged-in users can create posts/comments.
   •	Notifications page restricted to owner.

6) Mention parsing & notifications
   •	Mentions appear as @username in post/comment body (regex: /(^|[^a-zA-Z0-9_])@([a-z0-9_]{3,30})\b/ case-insensitive).
   •	On save of Post or Comment:
    1.	Parse body → set of mentioned usernames.
    2.	Resolve to user IDs (ignore non-existent users; ignore self-mention).
    3.	Create Notification per recipient with:
          •	type=mention, entity_type=post/comment, entity_id=the saved entity id, actor_user_id=current user.
          •	When a comment is created on someone’s post, create Notification for post owner with type=comment_on_your_post (skip if actor==owner).
          •	Notification list shows newest first; mark as read via POST to /notifications/:id/read.

7) Feed
   •	FeedController::index: latest posts (paginated), eager-load author, show top N recent comments (N=3).
   •	Use ORM containments; default page size 20.

8) Validation (selected)
   •	User:
   •	email: required, valid, unique.
   •	username: required, unique, lowercase [a-z0-9_]{3,30}.
   •	password: min 8 chars (letters+numbers), only at creation/reset.
   •	Post: title 1..160, body 1..10,000.
   •	Comment: body 1..2,000.

9) UI (templates)
   •	Layout with nav: Home, New Post, Notifications (unread badge), Profile dropdown.
   •	/ feed: card per post (author username → profile link, created time, title, excerpt, comment count).
   •	Post page: full body, comments list, add comment form.
   •	Notifications page: grouped by day, link to related post/comment, “Mark as read” buttons.

10) Emails (dev via Mailpit)
    •	Templates for: Verify email, Password reset.
    •	Env vars: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, APP_URL.

11) Migrations (high level)

Use bake to generate, then edit as needed:
•	CreateUsers, CreatePosts, CreateComments, CreateNotifications, CreatePasswordResets
•	Add unique indexes and enums (or varchar + CHECK if enums are awkward).

12) Tests (must pass)
    •	Auth: register→verify→login happy path; cannot login if not verified.
    •	Posts: create/list/view; only owner can edit/delete.
    •	Comments: add; only owner can delete.
    •	Mentions: create post/comment with @bob → notification for bob.
    •	Comment notification: commenting on alice’s post → alice gets notification.
    •	Notifications mark-as-read.
    •	Validation edge cases (duplicate email/username).

13) Definition of Done
    •	All routes implemented with CSRF & FormProtection.
    •	DB migrations applied; seeds create 3 demo users + 5 posts + comments.
    •	Static analysis (phpstan level 5+) clean; phpcs passes; test suite green.
    •	Basic 404/403 handling and flash messages.
    •	README updated with setup & DDEV commands.

⸻

Code scaffolding plan (Claude should follow this order)

Step 0 – Dependencies & config
•	Ensure cakephp/authentication, cakephp/authorization, cakephp/migrations installed.
•	Wire Authentication/Authorization middleware in Application.php.
•	Configure Email using env and add dev Mailpit note in README.

Step 1 – Migrations
•	Create 5 migrations exactly per §3.
•	Run: ddev exec bin/cake migrations migrate.
•	Add Seeds for 3 users (alice, bob, carol), 5 posts, 8 comments.

Step 2 – Bake models/controllers/templates
•	Users, Posts, Comments, Notifications.
•	Add associations & containments.

Step 3 – Auth flows
•	Register + email verification.
•	Login/logout; password reset.
•	Guard routes (only verified users can post/comment).

Step 4 – Posts & comments
•	CRUD, validation, policy checks.
•	Post view shows comments + add form.

Step 5 – Mentions & notifications
•	Implement mention parser as a small service (e.g., src/Service/MentionService.php).
•	Hook into afterSave() in PostsTable and CommentsTable to create notifications.
•	Notifications index + mark-read action.

Step 6 – Feed & profiles
•	/ feed list (paginate, newest first).
•	/u/:username profile with that user’s posts.

Step 7 – Tests & quality
•	PHPUnit tests per §12.
•	phpcs & phpstan config touches.

⸻

Snippets Claude can reuse

MentionService (core logic)

// src/Service/MentionService.php
namespace App\Service;

class MentionService
{
private const REGEX = '/(^|[^a-zA-Z0-9_])@([a-z0-9_]{3,30})\\b/i';

    /** @return string[] unique lowercase usernames */
    public function extract(string $text): array {
        preg_match_all(self::REGEX, $text, $m);
        $raw = array_map(fn($u) => strtolower($u), $m[2] ?? []);
        return array_values(array_unique($raw));
    }
}

Creating notifications after save (example for CommentsTable)

// src/Model/Table/CommentsTable.php -> afterSave
use Cake\ORM\TableRegistry;

public function afterSave(\Cake\Event\EventInterface $event, \App\Model\Entity\Comment $comment, \ArrayObject $options)
{
$users = TableRegistry::getTableLocator()->get('Users');
$notifications = TableRegistry::getTableLocator()->get('Notifications');
$posts = TableRegistry::getTableLocator()->get('Posts');

    // Mentions
    $mentions = (new \App\Service\MentionService())->extract((string)$comment->body);
    if ($mentions) {
        $mentionedUsers = $users->find()->where(['username IN' => $mentions])->all();
        foreach ($mentionedUsers as $u) {
            if ($u->id === $comment->user_id) continue;
            $n = $notifications->newEntity([
                'recipient_user_id' => $u->id,
                'actor_user_id' => $comment->user_id,
                'type' => 'mention',
                'entity_type' => 'comment',
                'entity_id' => $comment->id,
            ]);
            $notifications->save($n);
        }
    }

    // Notify post owner (comment_on_your_post)
    $post = $posts->get($comment->post_id);
    if ($post->user_id !== $comment->user_id) {
        $n = $notifications->newEntity([
            'recipient_user_id' => $post->user_id,
            'actor_user_id' => $comment->user_id,
            'type' => 'comment_on_your_post',
            'entity_type' => 'comment',
            'entity_id' => $comment->id,
        ]);
        $notifications->save($n);
    }
}

Routes (add to config/routes.php)

$routes->connect('/', ['controller' => 'Feed', 'action' => 'index']);
$routes->connect('/u/:username', ['controller' => 'Profiles', 'action' => 'view'])
->setPass(['username']);

MySQL username/email rules (UsersTable::validationDefault)

$validator
->email('email')->requirePresence('email')->notEmptyString('email')
->scalar('username')->lengthBetween('username',[3,30])
->regex('username','/^[a-z0-9_]+$/')->requirePresence('username')->notEmptyString('username');


⸻

Kickoff prompt for Claude Code (paste this in the PhpStorm Claude panel)

You are helping me implement SPEC.md in this repository using CakePHP 5 and MySQL on DDEV.
Rules:
1.	Read SPEC.md fully. Confirm understanding and propose a step-by-step plan.
2.	Work in small steps. Before changing files, state what you will change. After each step, show a git-style diff of all changes.
3.	Use bake/migrations where specified. Use Authentication+Authorization plugins.
4.	Use MySQL-safe schema; add needed indexes.
5.	Add/update tests; ensure ddev exec vendor/bin/phpunit passes before moving on.
6.	Don’t overwrite unrelated code; keep changes minimal; follow Cake conventions.
7.	When something in SPEC is ambiguous, suggest a sensible default and proceed.

First task:
•	Install/confirm cakephp/authentication, cakephp/authorization, and cakephp/migrations.
•	Wire middleware in src/Application.php.
•	Add email transport via env in config/app.php or app_local.php (SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, APP_URL).
•	Update README with local setup & Mailpit instructions.
Then stop and show diffs + commands to run (ddev exec bin/cake ...).

⸻

