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

use Mindy\Form\ModelForm;
use Modules\Comments\Models\BaseComment;

class CommentForm extends ModelForm
{
    public function setModel(BaseComment $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }
}
