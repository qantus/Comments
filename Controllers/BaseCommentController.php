<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 11/09/14.09.2014 12:35
 */

namespace Modules\Comments\Controllers;

use Mindy\Base\Mindy;
use Mindy\Helper\Json;
use Mindy\Orm\Model;
use Mindy\Pagination\Pagination;
use Modules\Comments\CommentsModule;
use Modules\Comments\Forms\CommentForm;
use Modules\Comments\Models\BaseComment;
use Modules\Core\Controllers\CoreController;

abstract class BaseCommentController extends CoreController
{
    public $toLink = 'owner_id';

    public $template = 'list.html';

    /**
     * @return \Modules\Comments\Models\BaseComment
     */
    abstract public function getModel();

    /**
     * @param $model
     * @param $toLink
     * @return CommentForm
     */
    public function getForm($model, $toLink)
    {
        $module = $this->getModel()->getModule();
        if (property_exists($module, 'commentForm') && $module->commentForm) {
            $commentClass = $module->commentForm;
        } else {
            $commentClass = 'Modules\Comments\Forms\CommentForm';
        }
        return new $commentClass(['model' => $model, 'toLink' => $toLink]);
    }

    public function getComments(Model $model)
    {
        $qs = $this->getModel()->objects()->nospam()->published();
        $pager = new Pagination($this->processComments($model, $qs));
        $models = $pager->paginate();
        return [$models, $pager];
    }

    public function getTemplate($name = null)
    {
        return 'comments/' . ($name === null) ? $name : $this->template;
    }

    public function internalActionList(Model $model)
    {
        list($models, $pager) = $this->getComments($model);
        if ($this->r->isAjax) {
            echo $this->json($pager->toJson());
        } else {
            echo $this->render($this->getTemplate(), [
                'comments' => $models,
                'form' => new CommentForm([
                    'model' => $this->getModel(),
                    'toLink' => $this->toLink
                ])
            ]);
        }
    }

    public function processForm(Model $model, BaseComment $instance)
    {
        $form = $this->getForm($instance, $this->toLink);
        $post = array_merge_recursive($_POST, [$form->classNameShort() => [$this->toLink => $model->pk]]);
        if ($this->r->isPost && $form->populate($post)->isValid()) {
            $form->instance = $this->processComment($form->getInstance());
            return [$form->save(), $form->getInstance()];
        }
        return [false, null];
    }

    public function internalActionSave(Model $model)
    {
        $this->ajaxValidation($model);
        if ($this->r->isPost) {
            list($isSaved, $instance) = $this->processForm($model, $this->getModel());
            if ($isSaved) {
                if ($instance->is_published) {
                    $this->r->flash->success(CommentsModule::t('The comment is successfully added'));
                } else {
                    $this->r->flash->success(CommentsModule::t('Your comment will appear on the website after being moderated'));
                }
                $this->redirectNext();
            }

            if ($this->r->isAjax) {
                echo $this->json([
                    'success' => $isSaved,
                    'model' => $instance->toJson()
                ]);
            } else {
                echo $this->render($this->getTemplate($isSaved ? 'success.html' : 'failed.html'), [
                    'model' => $instance
                ]);
            }
        } else {
            $this->error(400);
        }
    }

    public function ajaxValidation($model)
    {
        if ($this->r->isPost && isset($_POST['ajax_validation'])) {
            $instance = $this->getModel();
            $form = $this->getForm($instance, $this->toLink);
            $attributes = array_merge($_POST, [$this->toLink => $model->pk]);
            $form->populate($attributes)->isValid();
            echo Json::encode($form->getErrors());
            Mindy::app()->end();
        }
    }

    /**
     * @param \Mindy\Orm\Model $model
     * @param \Mindy\Orm\Manager|\Mindy\Orm\QuerySet $qs
     * @return \Mindy\Orm\Manager|\Mindy\Orm\QuerySet
     */
    abstract public function processComments(Model $model, $qs);

    /**
     * @param BaseComment $model
     * @return BaseComment
     */
    abstract public function processComment(BaseComment $model);
}
