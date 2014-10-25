<?php

namespace Phpmvc\Comment;

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
    public function viewAction()
    {
        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        $all = $comments->findByPageIdentifier($this->di->comments->getPageIdentifier());

        $this->views->add('comment/comments', [
            'comments' => $all,
            'validatedComment' => $comments->getValidatedComment(),
            'defaultAvatar' => urlencode($this->di->url->asset("img/no-avatar.png")),
            'title' => $this->theme->getVariable('title')
        ]);
    }


    /**
     * Edit a comment.
     *
     * @return void
     */
    public function editAction($pageIdentifier, $postId)
    {
        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        $comment = $comments->findComment($pageIdentifier, $postId);

        if($comment) {
            $comment = array_merge($comment, ['output' => '']);
            $this->theme->addJavaScript('js/comments.js');
            $this->theme->setTitle('Ändra kommentar');
            $this->views->add('comment/form', $comment);
        } else {
           $this->views->add('error/404');
        }

    }

    /**
     * Edit a comment.
     *
     * @return void
     */
    public function saveAction()
    {
        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);


        $isPosted = $this->request->getPost('doEdit');

            
            if($isPosted) {
                
                $pageIdentifier = $this->request->getPost('digest');
                $postId = $this->request->getPost('id');

                $tmpComment = $comments->findComment($pageIdentifier, $postId);

                $comment = [
                    'content'        => $this->request->getPost('content'),
                    'name'           => $this->request->getPost('name'),
                    'web'            => $this->request->getPost('web'),
                    'mail'           => $this->request->getPost('mail'),
                    'updated'        => time()
                ];
                

                $comment = array_merge($tmpComment, $comment);

                if( $comments->validate($comment) ) {
                    $comments->updateComment($pageIdentifier, $postId, $comment);
                    $this->response->redirect($this->request->getPost('redirect'));
                } else {
                    $this->theme->setTitle('Ett fel uppstod');
                    $this->views->add('comment/invalid', [
                        'validationErrors' => $comments->getValidationErrors(),
                    ]);
                }
            }
        
    }


    /**
     * Add a comment.
     *
     * @return void
     */
    public function addAction()
    {

        $isPosted = $this->request->getPost('doCreate');
        
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }

        $comment = [
            'pageIdentifier' => $this->request->getPost('digest'),
            'content'        => $this->request->getPost('content'),
            'name'           => $this->request->getPost('name'),
            'web'            => $this->request->getPost('web'),
            'mail'           => $this->request->getPost('mail'),
            'timestamp'      => time(),
            'ip'             => $this->request->getServer('REMOTE_ADDR')
        ];

        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        if( $comments->validate($comment) ) {
            $comments->add($comment);
            $this->response->redirect($this->request->getPost('redirect'));
        } else {
            $this->theme->setTitle('Ett fel uppstod');
            $this->views->add('comment/invalid', [
                'validationErrors' => $comments->getValidationErrors(),
            ]);
        }
    }



    /**
     * Remove all comments.
     *
     * @return void
     */
    public function removeAllAction()
    {
        $isPosted = $this->request->getPost('doRemoveAll');
        
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }

        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        $comments->deleteAll();

        $this->response->redirect($this->request->getPost('redirect'));
    }

    /**
     * Remove a comment.
     *
     * @return void
     */
    public function removeAction($pageIdentifier, $postId)
    {
        
        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        $comments->delete($pageIdentifier, $postId);


        $this->response->redirect($_SERVER['HTTP_REFERER']);
    }

}
