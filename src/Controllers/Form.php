<?php

namespace Leeroy\Forms\Controllers;

use Leeroy\Forms\Types\Settings;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\Form as FormModel;

class Form extends AppController
{
    /**
     *
     * Single form
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return FormModel|null
     * @throws DatabaseException
     *
     */
    public function form(mixed $obj, Collection $args, Context $context): ?FormModel
    {
        return (new FormModel())::getById($args->get('handle'));
    }

    /**
     *
     * Get all forms
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return Collection|null
     * @throws DatabaseException
     * @throws ACLException
     * @throws PermissionException
     *
     */
    public function forms(mixed $obj, Collection $args, Context $context): ?Collection
    {
        return (new FormModel())->getList();
    }

    /**
     *
     * Create form
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     */
    public function createForm(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormModel())->create(
            $args->get('handle'),
            $args->get('title'),
            $args->get('form_layout_id'),
            new Settings($args->get('settings'))
        );
    }

    /**
     *
     * Update form
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     * @throws DatabaseException
     */
    public function updateForm(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormModel())->update(
            $args->get('id'),
            $args->get('handle'),
            $args->get('title'),
            $args->get('form_layout_id'),
            $args->get('settings')
        );
    }

    /**
     *
     * Delete form
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     * @throws DatabaseException
     */
    public function deleteForm(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormModel())->removeById($args->get('id'));
    }
}