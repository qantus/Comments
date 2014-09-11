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

use Mindy\Orm\Model;
use Mindy\Pagination\Pagination;
use Modules\Comments\Forms\CommentForm;
use Modules\Comments\Models\BaseComment;
use Modules\Core\Controllers\CoreController;

abstract class BaseCommentController extends CoreController
{
    public $toLink = 'owner_id';

    /**
     * @return \Modules\Comments\Models\BaseComment
     */
    abstract public function getModel();

    /**
     * @return CommentForm
     */
    public function getForm($model, $toLink)
    {
        return new CommentForm(['model' => $model, 'toLink' => $toLink]);
    }

    public function getComments(Model $model)
    {
        $qs = $this->getModel()->objects()->notspam()->published();
        $pager = new Pagination($this->processComments($model, $qs));
        $models = $pager->paginate();
        return [$models, $pager];
    }

    public function getTemplate($name)
    {
        return 'comments/' . $name;
    }

    public function internalActionList(Model $model)
    {
        list($models, $pager) = $this->getComments($model);
        if($this->r->isAjax) {
            echo $this->json($pager->toJson());
        } else {
            echo $this->render($this->getTemplate('list.html'), [
                'models' => $models,
                'form' => new CommentForm
            ]);
        }
    }

    public function processForm(Model $model, BaseComment $instance)
    {
        $form = $this->getForm($instance, $this->toLink);
        $attributes = array_merge($_POST, [$this->toLink => $model->pk]);
        if($this->r->isPost && $form->setAttributes($attributes)->isValid()) {
            $instance = $this->processComment($form->getInstance());
            return [$instance->save(), $instance];
        }
        return [false, null];
    }

    public function internalActionSave(Model $model)
    {
        if($this->r->isPost) {
            list($isSaved, $instance) = $this->processForm($model, $this->getModel());
            if($isSaved) {
                $this->r->flash->success('Комментарий успешно добавлен');
                $this->redirectNext();
            }

            if($this->r->isAjax) {
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
