<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 11/09/14.09.2014 17:52
 */

namespace Modules\Comments\Admin;

use Modules\Admin\Components\ModelAdmin;

abstract class BaseCommentAdmin extends ModelAdmin
{
    public function getColumns()
    {
        return ['id', 'username', 'email', 'user', 'created_at', 'published_at', 'is_spam', 'is_published'];
    }
}
