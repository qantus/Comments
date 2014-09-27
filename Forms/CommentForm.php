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
use Mindy\Form\Validator\RequiredValidator;

class CommentForm extends ModelForm
{
    public $model;

    public $toLink;

    public $exclude = ['is_spam', 'is_published', 'user', 'created_at', 'updated_at'];

    public function getFields()
    {
        return array_merge(parent::getFields(), [
            'parent' => [
                'class' => HiddenField::className()
            ]
        ]);
    }

    public function initFields()
    {
        parent::initFields();
        if (Mindy::app()->user->isGuest) {
            $this->_fields['username']->required = true;
            $this->_fields['username']->validators[] = new RequiredValidator();
            $this->_fields['email']->required = true;
            $this->_fields['email']->validators[] = new RequiredValidator();
        }
    }

    public function init()
    {
        if(!$this->getModel()) {
            d(debug_backtrace());
        }
        $meta = $this->getModel()->getMeta();
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

    public function save()
    {
        if($this->getInstance()->getIsNewRecord()) {
            $saved = parent::save();
            Mindy::app()->mail->fromCode('comments.new_comment', Mindy::app()->managers, [
                'data' => $this->getInstance()
            ]);
            return $saved;
        } else {
            return parent::save();
        }
    }
}
