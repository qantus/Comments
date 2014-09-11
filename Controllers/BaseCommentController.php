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
    /**
     * @return \Modules\Comments\Models\BaseComment
     */
    abstract public function getModel();

    /**
     * @return CommentForm
     */
    public function getForm()
    {
        return new CommentForm;
    }

    public function getComments(Model $model)
    {
        $qs = $this->getModel()->objects()->notspam()->published();
        $pager = new Pagination($this->processComments($model, $qs));
        $models = $pager->paginate();
        return [$models, $pager];
    }

    public function internalActionList(Model $model)
    {
        list($models, $pager) = $this->getComments($model);
        if($this->r->isAjax) {
            echo $this->json($pager->toJson());
        } else {
            echo $this->render('comments/list.html', [
                'models' => $models
            ]);
        }
    }

    public function actionSave()
    {
        $form = $this->getForm();
        $form->setModel($this->getModel());
        if($this->r->isPost && $form->setAttributes($_POST)->isValid()) {
            $model = $this->processComment($form->getInstance());
            $isSaved = $model->save();
            if($this->r->isAjax) {
                echo $this->json([
                    'success' => $isSaved,
                    'model' => $model->toJson()
                ]);
            } else {
                echo $this->render($isSaved ? 'comments/success.html' : 'comments/failed.html', [
                    'model' => $model
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
