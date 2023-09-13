<?php

namespace Leeroy\Forms\Controllers;

use Leeroy\Forms\Services\Recaptcha;
use Leeroy\Forms\Types\Settings;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\Form as FormModel;
use Leeroy\Forms\Models\FormEntry;

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
            $args->get('fields'),
            new Settings($args->get('settings', []))
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
            $args->get('fields'),
            new Settings($args->get('settings', []))
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
        return (new FormModel())->removeByIds($args->get('ids')->unwrap());
    }

    /**
     *
     * Get recaptcha tag
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return string
     */
    public function recaptchaTag(mixed $obj, Collection $args, Context $context): string
    {
        return (new Recaptcha())->recaptchaTag($args->get('version'), $args->get('site_key', ''));
    }

    /**
     *
     * Get recaptcha script(s)
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return string
     */
    public function recaptchaScript(mixed $obj, Collection $args, Context $context): string
    {
        return (new Recaptcha())->recaptchaScript($args->get('form_id'), $args->get('version', ''));
    }

    /**
     *
     * Get success email
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return FormEntry
     *
     * @throws DatabaseException
     *
     */
    public function formSuccessEmail(mixed $obj, Collection $args, Context $context): FormEntry
    {
        return (new FormModel())->getSuccessEmailByHandle($args->get('handle', 'default'));
    }

    /**
     *
     * Create success email
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     *
     */
    public function createFormSuccessEmail(mixed $obj, Collection $args, Context $context): Bool
    {
        return (new FormModel())->createSuccessEmail(
            $args->get('form_handle'),
            $args->get('locale'),
            $args->get('title'),
            $args->get('template'),
            $args->get('content'),
            $args->get('site_id')
        );
    }

    /**
     *
     * Update success email
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws DatabaseException
     *
     */
    public function updateFormSuccessEmail(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormModel())->updateSuccessEmail(
            $args->get('id'),
            $args->get('form_handle'),
            $args->get('locale'),
            $args->get('title'),
            $args->get('template'),
            $args->get('dates'),
            $args->get('content'),
            $args->get('site_id')
        );
    }

    /**
     *
     * Delete success email
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws DatabaseException
     *
     */
    public function deleteFormSuccessEmail(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormModel())->removeSuccessEmailByHandle($args->get('form_handle'));
    }
}