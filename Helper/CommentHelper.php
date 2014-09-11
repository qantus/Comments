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
 * @date 11/09/14.09.2014 13:35
 */

namespace Modules\Comments\Helper;


use Mindy\Orm\HasManyManager;
use Mindy\Pagination\Pagination;
use Mindy\Utils\RenderTrait;

class CommentHelper
{
    use RenderTrait;

    public static function render_comments($template, HasManyManager $manager)
    {
        $pager = new Pagination($manager->getQuerySet());
        return self::renderStatic($template, [
            'comments' => $pager->paginate(),
            'pager' => $pager
        ]);
    }
}
