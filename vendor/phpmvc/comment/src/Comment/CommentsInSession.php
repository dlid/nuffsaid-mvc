<?php

namespace Phpmvc\Comment;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentsInSession implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    private $lastValidatedComment = null;

    public function getAutoSavedFields() {
        return $this->session->get('commentSavedFields', []);
    }

    /**
     * Validate a new comment.
     *
     * @param array $comment with all details.
     * 
     * @return void
     */
    public function validate($comment)
    {
        $this->session->set('last_comment', []);
        $this->session->set('invalid_comment_errors', []);
        $errors = [];

        $comment['content'] || $errors[] = 'Du skrev ingen kommentar';
        $comment['name'] || $errors[] = 'Du skrev inte ditt namn eller ett alias';

        if (!empty($errors)) {
            $this->session->set('invalid_comment_errors', $errors);
            $this->session->set('last_comment', $comment);
            return false;
        }
        return true;
    }

    /**
     * Add a new comment.
     *
     * @param array $comment with all details.
     * 
     * @return void
     */
    public function add($comment)
    {
        $comment['id'] = md5( implode(',', $comment));
        
        $newContent = $this->di->textFilter->doFilter($comment['content'], 'clickable,striphtml,nl2br');

        if( empty( $newContent)) {
            $comment['content'] = htmlentities($comment['content'], null, 'utf-8');
        } else {
            $comment['content'] = $newContent;
        }

        $comments = $this->session->get('comments', []);

        if (!isset($comments[$comment['pageIdentifier']])) {
            $comments[$comment['pageIdentifier']] = [];
        }


        $this->session->set('commentSavedFields', [
            'mail' => $comment['mail'],
            'name' => $comment['name']
        ]);

        $this->session->set('last_comment', array_merge($comment, [ 'content' => null]));

        $comments[$comment['pageIdentifier']][] = $comment;
        $this->session->set('comments', $comments);
    }


    /**
     * Find and return all comments.
     *
     * @return array with all comments.
     */
    public function findAll()
    {
        $all = [];
        $commentsPerPage = $this->session->get('comments', []);

        foreach( $commentsPerPage as $comments) {
            $all = array_merge($all, $comments);
        }

        return $all;
    }

    /**
     * Find and return all comments.
     *
     * @return array with all comments.
     */
    public function findComment($pageIdentifier, $commentId)
    {
        $all = $this->session->get('comments', []);

        if(isset($all[$pageIdentifier]) && isset($all[$pageIdentifier])) {
            foreach( $all[$pageIdentifier] as $index => $comment ) {
                if( $comment['id'] == $commentId) {
                    return $comment;
                }
            }
        }

        return null;
    }


    /**
     * Find and return all comments.
     *
     * @return array with all comments.
     */
    public function updateComment($pageIdentifier, $commentId, $updatedFields)
    {
        $all = $this->session->get('comments', []);

        if(isset($all[$pageIdentifier]) && isset($all[$pageIdentifier])) {
            foreach( $all[$pageIdentifier] as $index => $comment ) {
                if( $comment['id'] == $commentId) {
                    $all[$pageIdentifier][$index] = array_merge($comment, $updatedFields);
                    $this->session->set('comments', $all);
                    $this->session->set('last_comment', []);
                    return;
                }
            }
        }
    }

    /**
     * Delete a single comment
     *
     * @return array with all comments.
     */
    public function delete($pageIdentifier, $commentId)
    {
        $all = $this->session->get('comments', []);

        if(isset($all[$pageIdentifier]) && isset($all[$pageIdentifier])) {
            foreach( $all[$pageIdentifier] as $index => $comment ) {
                if( $comment['id'] == $commentId) {
                    $first = array_slice($all[$pageIdentifier], 0, $index);
                    $second = array_slice($all[$pageIdentifier], $index+1);
                    $all[$pageIdentifier] = array_merge($first, $second);
                    $all = $this->session->set('comments', $all);
                    return;
                }
            }

        }
    }

    public function findByPageIdentifier($pageIdentifier) {

        $all = $this->session->get('comments', []);
        if(isset($all[$pageIdentifier])) {
            return $all[$pageIdentifier];
        }
        return [];
    }

    /**
     * Get the most recently validated comment
     *
     * @return array with all comments.
     */
    public function getValidatedComment()
    {
        if($this->lastValidatedComment) {
            return $this->lastValidatedComment;
        }

        $this->lastValidatedComment = $this->session->get('last_comment', []);
        if( !$this->lastValidatedComment ) {
            return [];
        } 

        return $this->lastValidatedComment;
    }

    /**
     * Get the validation errors for the most recently posted comment
     *
     * @return array with all errors.
     */
    public function getValidationErrors()
    {
        $errors = $this->session->get('invalid_comment_errors');
        $this->session->set('invalid_comment_errors', []);

        return $errors;
    }

    /**
     * Delete all comments.
     *
     * @return void
     */
    public function deleteAll()
    {
        $this->session->set('comments', []);
    }
}
