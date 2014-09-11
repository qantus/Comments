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
 * @date 11/09/14.09.2014 12:34
 */

namespace Modules\Comments\Models;

use Mindy\Base\Mindy;
use Mindy\Orm\Fields\BooleanField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\EmailField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\TreeModel;
use Modules\Comments\Helper\Akismet;
use Modules\User\Models\User;

abstract class BaseComment extends TreeModel
{
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'username' => [
                'class' => CharField::className(),
                'null' => true
            ],
            'email' => [
                'class' => EmailField::className(),
                'null' => true
            ],
            'user' => [
                'class' => ForeignField::className(),
                'modelClass' => User::className(),
                'null' => true
            ],
            'is_spam' => [
                'class' => BooleanField::className(),
            ],
            'is_published' => [
                'class' => BooleanField::className(),
                'default' => true,
            ],
            'comment' => [
                'class' => TextField::className(),
                'null' => false
            ],
            'created_at' => [
                'class' => DateTimeField::className(),
                'autoNowAdd' => true
            ],
            'updated_at' => [
                'class' => DateTimeField::className(),
                'autoNow' => true
            ],
            'published_at' => [
                'class' => DateTimeField::className(),
                'editable' => false
            ],
        ]);
    }

    public function beforeSave($owner, $isNew)
    {
        $akisment = Mindy::app()->getModule('Comments')->akisment;
        if (!empty($akisment) && count($akisment) == 2) {
            list($site, $key) = $akisment;

            $akismet = new Akismet($site, $key);
            if (!$akismet->isKeyValid()) {
                Mindy::app()->logger->error('Invalid akisment key', 'comments');
            } else {
                if ($user = $owner->user) {
                    $akismet->setCommentAuthor($user->username);
                    $akismet->setCommentAuthorEmail($user->email);
                    if (method_exists($user, 'getAbsoluteUrl')) {
                        $akismet->setCommentAuthorURL($this->wrapUrl($user->getAbsoluteUrl()));
                    }
                } else {
                    $akismet->setCommentAuthor($owner->username);
                    $akismet->setCommentAuthorEmail($owner->email);
                    $akismet->setCommentAuthorURL(null);
                }
                $akismet->setCommentContent($owner->comment);
                $akismet->setPermalink($owner->getRelationUrl());
                $owner->is_spam = $akismet->isCommentSpam();
                $owner->is_published = !$owner->is_spam;
            }
        }

        if($owner->is_published) {
            $owner->published_at = time();
        } else {
            $owner->published_at = null;
        }
    }

    protected function wrapUrl($url)
    {
        return Mindy::app()->request->http->getAbsoluteUrl($url);
    }

    /**
     * @return BaseComment
     */
    abstract public function getRelation();

    public function getRelationUrl()
    {
        $relation = $this->getRelation();
        if(method_exists($relation, 'getAbsoluteUrl')) {
            return $this->wrapUrl($relation->getAbsoluteUrl());
        }

        return null;
    }

    /**
     * @param null $instance
     * @return \Mindy\Orm\TreeManager|CommentManager
     */
    public static function objectsManager($instance = null)
    {
        $className = get_called_class();
        return new CommentManager($instance ? $instance : new $className);
    }
}
