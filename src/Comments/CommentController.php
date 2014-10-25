<?php

namespace Anax\Comments;
use \Anax\Comments\CommentsInSession;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;


    /**
     * View all comments.
     *
     * @return void
     */
    public function allAction()
    {
         $this->theme->addJavaScript('js/comments.js');
        $all = $this->comments->findAll();
        $this->theme->setTitle('Alla kommentarer');
        $this->views->add('comment/comments', [
            'comments' => $all,
            'all' => true,
            'defaultAvatar' => urlencode($this->di->url->asset("img/no-avatar.png")),
            'title' => $this->theme->getVariable('title')
        ]);

    }


    /**
     * View all comments for a page
     *
     * @return void
     */
    public function viewAction($region = 'main')
    {
        $all = $this->comments->findByUrl($this->di->comments->getPageUrl());

        $this->views->add('comment/comments', [
            'comments' => $all,
            'defaultAvatar' => urlencode($this->di->url->asset("img/no-avatar.png")),
            'title' => $this->theme->getVariable('title')
        ], $region);
    }


    /**
     * Edit a comment.
     *
     * @return void
     */
    public function editAction($postId)
    {
   
        $comment = $this->comments->find($postId);

        if($comment) {
            $this->theme->setTitle('Redigera kommentar');
            
            $prop = $comment->getProperties();

            $page = $this->pages->find($prop['page_id'])->getProperties();
            $pageUrl = $page['url'];
   

            $this->theme->addJavaScript('js/comments.js');

            $form = new CFormComment($this->di, $pageUrl, null, $this->request->getCurrentUrl() );
            $form->setValues($comment);

            $status = $form->Check();

            // What to do if the form was submitted?
            if($status === true) {
            

               $this->comments->update([
                    'comment'        => $this->request->getPost('comment'),
                    'name'           => $this->request->getPost('name'),
                    'web'            => $this->request->getPost('web'),
                    'email'          => $this->request->getPost('email'),
                    'updated'        => date('Y-m-d H:i:s')
                ]);

                $this->session->set('comment-name', $this->request->getPost('name'));
                $this->session->set('comment-email', $this->request->getPost('email'));
                $this->session->set('comment-web', $this->request->getPost('web'));

                // Make sure we redirect to the new comment's location
                $redirect = $this->url->updateUrl($this->request->getPost('page'), [ 'fragment' => 'comment' . $this->comments->id]);
                header("Location: " . $redirect);

                exit;
            }
            else if($status === false){
                $form->saveInSession = true;
                header("Location: " . $this->request->getCurrentUrl( ));
                exit;
            }

            $this->views->addString($form->GetHTML(), 'main');

        } else {
            $this->theme->setTitle('404 Not found');
           $this->views->add('error/404');
        }

    }


    /**
     * Add a comment.
     *
     * @return void
     */
    public function addAction($region = 'main')
    {

        $this->theme->addJavaScript('js/comments.js');
        $pageUrl = $this->comments->getPageUrl();

        $form = new CFormComment($this->di, $pageUrl, $this->request->getCurrentUrl() . '#comments');
        $status = $form->Check();

        // What to do if the form was submitted?
        if($status === true) {

            $this->comments->create([
                'comment'        => $this->request->getPost('comment'),
                'name'           => $this->request->getPost('name'),
                'web'            => $this->request->getPost('web'),
                'email'          => $this->request->getPost('email'),
                'created'        => date('Y-m-d H:i:s'),
                'page_id'        => $this->pages->ensurePage($this->request->getPost('page'))
            ]);

            $this->session->set('comment-name', $this->request->getPost('name'));
            $this->session->set('comment-email', $this->request->getPost('email'));
            $this->session->set('comment-web', $this->request->getPost('web'));

            // Make sure we redirect to the new comment's location
            $redirect = $this->url->updateUrl($this->request->getPost('redirect'), [ 'fragment' => 'comment' . $this->comments->id]);
            header("Location: " . $redirect);

            exit;
        }
        else if($status === false){
            header("Location: " . $this->request->getPost('redirect'));
            exit;
        }

        $this->views->addString($form->GetHTML(), $region);

    }

    /**
     * Remove a comment.
     *
     * @return void
     */
    public function removeAction( $postId)
    {
        $this->comments->delete($postId);
        $this->response->redirect( $this->url->updateUrl($_SERVER['HTTP_REFERER'], array('fragment' => 'comments')) );
    }


    public function setupAction() {

        $this->db->setVerbose();

        $this->db->dropTableIfExists('comment')->execute();
        $this->db->dropTableIfExists('page')->execute();
     
         $this->db->createTable(
            'page',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'url' => ['varchar(255)', 'unique', 'not null'],
                'created' => ['datetime']
            ]
        )->execute();

        $this->db->execute('CREATE UNIQUE INDEX [IX_url] ON [page] ([url]);');

        $this->db->createTable(
            'comment',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'email' => ['varchar(80)'],
                'name' => ['varchar(80)', 'not null'],
                'web' => ['varchar(100)'],
                'comment' => ['text', 'not null'],
                'created' => ['datetime'],
                'updated' => ['datetime'],
                'page_id' => ['integer', 'not null', 'on conflict fail', 'constraint [FK_Page] references [page]([id])', 'on delete cascade', 'on update cascade']
            ]
        )->execute();

        exit;
        
    }

}
