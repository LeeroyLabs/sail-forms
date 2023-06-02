<?php

namespace Leeroy\Forms\Controllers;

use Leeroy\Forms\Types\Settings;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\FormType as FormTypeModel;

class FormType extends AppController
{
    /**
     *
     * Single form type
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return FormTypeModel|null
     * @throws DatabaseException
     *
     */
    public function formType(mixed $obj, Collection $args, Context $context): ?FormTypeModel
    {
        return (new FormTypeModel())::getById($args->get('handle'));
    }

    /**
     *
     * Get all form type
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
    public function formTypes(mixed $obj, Collection $args, Context $context): ?Collection
    {
        return (new FormTypeModel())->getList();
    }

    /**
     *
     * Create form type
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     */
    public function createFormType(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormTypeModel())->create(
            $args->get('handle'),
            $args->get('title'),
            $args->get('form_layout_id'),
            new Settings($args->get('settings'))
        );
    }

    /**
     *
     * Update form type
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     * @throws DatabaseException
     */
    public function updateFormType(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormTypeModel())->update(
            $args->get('id'),
            $args->get('handle'),
            $args->get('title'),
            $args->get('form_layout_id'),
            $args->get('settings')
        );
    }

    /**
     *
     * Delete form type
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     * @throws DatabaseException
     */
    public function deleteFormType(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormTypeModel())->removeById($args->get('id'));
    }
}