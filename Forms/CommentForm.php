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
 * @date 11/09/14.09.2014 14:44
 */

namespace Modules\Comments\Forms;

use Mindy\Base\Mindy;
use Mindy\Form\Fields\HiddenField;
use Mindy\Form\ModelForm;

class CommentForm extends ModelForm
{
    public $model;

    public $toLink;

    public $exclude = ['is_spam', 'is_published', 'user'];

    public function getFields()
    {
        return array_merge(parent::getFields(), [
            'parent' => [
                'class' => HiddenField::className()
            ]
        ]);
    }

    public function init()
    {
        $meta = $this->model->getMeta();
        $this->exclude[] = $meta->getForeignField($this->toLink)->name;
        if(!Mindy::app()->user->isGuest) {
            $this->exclude = array_merge($this->exclude, ['username', 'email']);
        }
        parent::init();
    }

    public function getModel()
    {
        return $this->model;
    }
}
